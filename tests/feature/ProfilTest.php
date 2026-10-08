<?php

use Tests\Support\RobotixTestCase;

final class ProfilTest extends RobotixTestCase
{
    private function idTarif(string $code): int
    {
        return (int) $this->db->table('Tarif')->where('code', $code)->get()->getRow('idTarif');
    }

    public function testVisiteurRedirigeVersLaConnexion(): void
    {
        $this->assertStringContainsString('compte/connexion', $this->get('compte/profil')->getRedirectUrl());
    }

    public function testLeProfilAfficheLesDonneesDeLAdherent(): void
    {
        $resultat = $this->withSession($this->sessionDe('client@robotix.test'))->get('compte/profil');

        $resultat->assertOK();
        $resultat->assertSee('Camille');
        $resultat->assertSee('Martin');
        $resultat->assertSee('client@robotix.test');
        $resultat->assertSee('Adulte');
        $resultat->assertSee('Passion');
        $resultat->assertSee('99,00 €');
        $resultat->assertSee($this->demoFr('2026-01-15'));
        $resultat->assertSee('Domestique');
        $resultat->assertSee('Atelier programmation Poppy');
        $resultat->assertSee('images/ui/avatar.svg');
        $resultat->assertSee('js/profil.js');
    }

    public function testUnAdministrateurNAPasDeProfilAdherent(): void
    {
        $this->withSession($this->sessionDe('admin@robotix.test'))->get('compte/profil')
            ->assertSessionHas('erreur', 'Le profil adhérent est réservé aux clients.');
    }

    public function testChangementDeFormuleEnAjax(): void
    {
        $resultat = $this->withSession($this->sessionDe('client@robotix.test'))
            ->withBodyFormat('json')
            ->post('compte/formule', ['idTarif' => $this->idTarif('premium')]);

        $resultat->assertOK();
        $json = json_decode($resultat->getJSON(), true);
        $this->assertTrue($json['succes']);
        $this->assertSame('Premium', $json['formule']);
        $this->assertSame(199.0, (float) $json['montant']);

        $id = $this->idUtilisateur('client@robotix.test');
        $this->assertSame($this->idTarif('premium'), (int) $this->db->table('Adhesion')->where(['idUtilisateur' => $id, 'annee' => (int) date('Y')])->get()->getRow('idTarif'));
    }

    public function testUneOptionNEstPasUneFormule(): void
    {
        $this->withSession($this->sessionDe('client@robotix.test'))
            ->withBodyFormat('json')
            ->post('compte/formule', ['idTarif' => $this->idTarif('garantie')])
            ->assertStatus(400);
    }

    public function testFormuleCreeeSiPasEncoreAdherentCetteAnnee(): void
    {
        $id = $this->idUtilisateur('jules.moreau@exemple.fr'); // pas d'adhésion en 2026
        $this->withSession(['idUtilisateur' => $id, 'nom' => 'Moreau', 'prenom' => 'Jules', 'role' => 'client'])
            ->withBodyFormat('json')
            ->post('compte/formule', ['idTarif' => $this->idTarif('decouverte')])
            ->assertOK();

        $this->assertSame(1, $this->db->table('Adhesion')->where(['idUtilisateur' => $id, 'annee' => (int) date('Y')])->countAllResults());
    }
}
