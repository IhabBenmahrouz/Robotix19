<?php

use Tests\Support\RobotixTestCase;

final class AdminJournalTest extends RobotixTestCase
{
    private function noter(string $type, string $table, string $description, int $nombre = 1): void
    {
        $admin = $this->idUtilisateur('admin@robotix.test');
        for ($i = 0; $i < $nombre; $i++) {
            $this->db->table('Journal')->set(['typeAction' => $type, 'tableCible' => $table, 'idCible' => 1, 'description' => $description, 'idUtilisateur' => $admin])
                ->set('dateAction', 'GETDATE()', false)->insert();
        }
    }

    public function testUneActionDAdministrationApparaitDansLeJournal(): void
    {
        $session = $this->sessionDe('admin@robotix.test');
        $this->withSession($session)->post('admin/evenements/' . $this->db->table('Evenement')->get()->getRow('idEvenement') . '/supprimer');

        $resultat = $this->withSession($session)->get('admin/journal');
        $resultat->assertOK();
        $resultat->assertSee('suppression');
        $resultat->assertSee('<code>Evenement</code>');
        $resultat->assertSee('Hugo Bernard');
    }

    public function testFiltreParActionEtParTable(): void
    {
        $this->noter('ajout', 'Animateur', 'Ligne animateur');
        $this->noter('suppression', 'Client', 'Ligne client');

        $resultat = $this->withSession($this->sessionDe('admin@robotix.test'))->get('admin/journal', ['type' => 'suppression']);
        $resultat->assertSee('Ligne client');
        $resultat->assertDontSee('Ligne animateur');

        $resultat = $this->withSession($this->sessionDe('admin@robotix.test'))->get('admin/journal', ['table' => 'Animateur']);
        $resultat->assertSee('Ligne animateur');
        $resultat->assertDontSee('Ligne client');
    }

    public function testUnFiltreInconnuEstIgnore(): void
    {
        $this->noter('ajout', 'Animateur', 'Ligne animateur');

        $this->withSession($this->sessionDe('admin@robotix.test'))->get('admin/journal', ['type' => "x' OR 1=1 --"])
            ->assertSee('Ligne animateur');
    }

    public function testPaginationParVingtCinq(): void
    {
        $this->noter('modification', 'Client', 'Ligne paginée', 30);

        $page1 = $this->withSession($this->sessionDe('admin@robotix.test'))->get('admin/journal');
        $this->assertSame(25, substr_count($page1->getBody(), 'Ligne pagin'));
        $page1->assertSee('class="pagination');

        $page2 = $this->withSession($this->sessionDe('admin@robotix.test'))->get('admin/journal', ['page' => 2]);
        $this->assertGreaterThanOrEqual(5, substr_count($page2->getBody(), 'Ligne pagin'));
    }

    public function testReserveALAdministrateur(): void
    {
        $this->get('admin/journal')->assertRedirect();
        $this->withSession($this->sessionDe('client@robotix.test'))->get('admin/journal')->assertRedirect();
    }
}
