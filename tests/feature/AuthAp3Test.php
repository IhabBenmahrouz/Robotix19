<?php

use Tests\Support\RobotixTestCase;

/**
 * AP3 : authentification par e-mail ou pseudo et redirection selon le rôle.
 */
final class AuthAp3Test extends RobotixTestCase
{
    private function connexion(string $identifiant): \CodeIgniter\Test\TestResponse
    {
        return $this->post('compte/connexion', ['identifiant' => $identifiant, 'motDePasse' => 'Robotix2026!']);
    }

    public function testLeFormulaireDemandeEmailOuPseudo(): void
    {
        $resultat = $this->get('compte/connexion');

        $resultat->assertSee('E-mail ou pseudo');
        $resultat->assertSee('name="identifiant"');
    }

    public function testConnexionParPseudoDUnMembre(): void
    {
        $resultat = $this->connexion('camille');

        $resultat->assertSessionHas('role', 'client');
        $resultat->assertSessionHas('profilClub', ['membre']);
        $this->assertStringEndsWith('club/membres', $resultat->getRedirectUrl());
    }

    public function testLePseudoEstInsensibleALaCasse(): void
    {
        $this->connexion('CAMILLE')->assertSessionHas('profilClub', ['membre']);
    }

    public function testUnAnimateurEstRedirigeVersSonEspace(): void
    {
        $resultat = $this->connexion('karim.h');

        $resultat->assertSessionHas('profilClub', ['animateur']);
        $this->assertStringEndsWith('club/animateur', $resultat->getRedirectUrl());
    }

    public function testConnexionParEmailToujoursPossible(): void
    {
        $this->assertStringEndsWith('admin/club/rapports', $this->connexion('admin@robotix.test')->getRedirectUrl());
    }

    public function testUnClientHorsDuClubVaSurSonProfil(): void
    {
        $resultat = $this->connexion('jules.moreau@exemple.fr');

        $resultat->assertSessionHas('profilClub', []);
        $this->assertStringEndsWith('compte/profil', $resultat->getRedirectUrl());
    }

    public function testRetourALaPageDemandee(): void
    {
        $resultat = $this->withSession(['redirection' => 'http://example.com/reservations'])->post('compte/connexion', [
            'identifiant' => 'camille', 'motDePasse' => 'Robotix2026!',
        ]);

        $this->assertSame('http://example.com/reservations', $resultat->getRedirectUrl());
    }

    public function testPseudoInconnuRefuse(): void
    {
        $resultat = $this->connexion('inconnu');

        $resultat->assertSessionHas('erreur', 'Identifiant ou mot de passe incorrect.');
        $resultat->assertSessionMissing('idUtilisateur');
    }

    public function testLaDeconnexionOublieLeProfilDuClub(): void
    {
        $this->withSession($this->sessionDe('client@robotix.test'))->post('compte/deconnexion')
            ->assertSessionMissing('profilClub');
    }
}
