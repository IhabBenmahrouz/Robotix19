<?php

use Tests\Support\RobotixTestCase;

final class AdminEvenementsTest extends RobotixTestCase
{
    private function admin(): array
    {
        return $this->sessionDe('admin@robotix.test');
    }

    private function idShowroom(string $ville): int
    {
        return (int) $this->db->table('Showroom')->where('ville', $ville)->get()->getRow('idShowroom');
    }

    private function evenement(array $surcharge = []): array
    {
        return $surcharge + [
            'titre' => 'Soirée portes ouvertes', 'description' => 'Tous les robots en démonstration.',
            'type' => 'demo', 'dateDebut' => $this->demo('2026-11-26T18:00'), 'dateFin' => $this->demo('2026-11-26T21:00'),
            'idShowroom' => (string) $this->idShowroom('Marseille'), 'idProduit' => '',
        ];
    }

    private function nombre(): int
    {
        return $this->db->table('Evenement')->countAllResults();
    }

    public function testVisiteurRedirigeVersLaConnexion(): void
    {
        $resultat = $this->get('admin/evenements');

        $resultat->assertRedirect();
        $this->assertStringContainsString('compte/connexion', $resultat->getRedirectUrl());
    }

    public function testClientRefuse(): void
    {
        $resultat = $this->withSession($this->sessionDe('client@robotix.test'))->get('admin/evenements');

        $resultat->assertRedirect();
        $resultat->assertSessionHas('erreur', 'Accès réservé aux administrateurs.');
    }

    public function testAdminVoitLePlanning(): void
    {
        $resultat = $this->withSession($this->admin())->get('admin/evenements');

        $resultat->assertOK();
        $resultat->assertSee('Gestion du planning', 'h1');
        $resultat->assertSee('Démonstration ARI');
        $resultat->assertSee('data-confirm=');
    }

    public function testCreationValideEtJournalisee(): void
    {
        $avant = $this->nombre();

        $resultat = $this->withSession($this->admin())->post('admin/evenements', $this->evenement());

        $resultat->assertRedirect();
        $resultat->assertSessionHas('succes');
        $this->assertSame($avant + 1, $this->nombre());

        $cree = $this->db->table('Evenement')->where('titre', 'Soirée portes ouvertes')->get()->getRowArray();
        $this->assertStringStartsWith($this->demo('2026-11-26 18:00'), $cree['dateDebut']); // le 26 novembre, pas d'inversion jour/mois
        $this->assertNull($cree['idProduit']);
        $this->assertSame(1, $this->db->table('Journal')->where('tableCible', 'Evenement')->where('idCible', $cree['idEvenement'])->countAllResults());
    }

    public function testFinAvantDebutRefusee(): void
    {
        $avant = $this->nombre();

        $this->withSession($this->admin())
            ->post('admin/evenements', $this->evenement(['dateFin' => $this->demo('2026-11-26T17:00')]))
            ->assertSessionHas('erreur', 'La fin doit être postérieure au début.');
        $this->assertSame($avant, $this->nombre());
    }

    public function testChevauchementDansLeMemeShowroomRefuse(): void
    {
        // « Démonstration ARI » occupe Marseille le 25/11/2026 de 14 h à 17 h
        $this->withSession($this->admin())
            ->post('admin/evenements', $this->evenement(['dateDebut' => $this->demo('2026-11-25T15:00'), 'dateFin' => $this->demo('2026-11-25T16:00')]))
            ->assertSessionHas('erreur', 'Ce showroom a déjà un événement sur ce créneau.');
    }

    public function testMemeCreneauDansUnAutreShowroomAccepte(): void
    {
        $this->withSession($this->admin())
            ->post('admin/evenements', $this->evenement([
                'dateDebut' => $this->demo('2026-11-25T15:00'), 'dateFin' => $this->demo('2026-11-25T16:00'),
                'idShowroom' => (string) $this->idShowroom('Paris'),
            ]))
            ->assertSessionHas('succes');
    }

    public function testTypeInconnuRefuse(): void
    {
        $this->withSession($this->admin())
            ->post('admin/evenements', $this->evenement(['type' => 'concert']))
            ->assertSessionHas('erreur');
    }

    public function testModification(): void
    {
        $id = (int) $this->db->table('Evenement')->where('titre', 'Démonstration ARI')->get()->getRow('idEvenement');

        $resultat = $this->withSession($this->admin())->post('admin/evenements/' . $id, $this->evenement([
            'titre' => 'Démonstration ARI (complet)', 'dateDebut' => $this->demo('2026-11-25T14:00'), 'dateFin' => $this->demo('2026-11-25T17:00'),
        ]));

        $resultat->assertSessionHas('succes'); // ne se chevauche pas avec lui-même
        $this->assertSame('Démonstration ARI (complet)', $this->db->table('Evenement')->where('idEvenement', $id)->get()->getRow('titre'));
    }

    public function testSuppression(): void
    {
        $id = (int) $this->db->table('Evenement')->where('titre', 'Démonstration ARI')->get()->getRow('idEvenement');

        $this->withSession($this->admin())->post('admin/evenements/' . $id . '/supprimer')->assertSessionHas('succes');
        $this->assertSame(0, $this->db->table('Evenement')->where('idEvenement', $id)->countAllResults());
    }

    public function testLeTitreEstEchappeALAffichage(): void
    {
        $this->withSession($this->admin())->post('admin/evenements', $this->evenement(['titre' => '<script>alert(1)</script>']));

        $resultat = $this->withSession($this->admin())->get('admin/evenements');
        $resultat->assertDontSee('<script>alert(1)</script>');
        $resultat->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;');
    }

    public function testFormulaireDeModificationPrerempli(): void
    {
        $id = (int) $this->db->table('Evenement')->where('titre', 'Démonstration ARI')->get()->getRow('idEvenement');

        $this->withSession($this->admin())->get('admin/evenements/' . $id . '/modifier')
            ->assertSee('value="' . $this->demo('2026-11-25T14:00') . '"');
    }
}
