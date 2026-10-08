<?php

use Tests\Support\RobotixTestCase;

/**
 * AP3 : pages invité, joueur (membre) et entraîneur (animateur).
 */
final class ClubTest extends RobotixTestCase
{
    private function idEvenement(string $titre): int
    {
        return (int) $this->db->table('Evenement')->where('titre', $titre)->get()->getRow('idEvenement');
    }

    // ---------- Page invité ----------

    public function testPageInviteDePresentationDuClub(): void
    {
        $resultat = $this->get('club');

        $resultat->assertOK();
        $resultat->assertSee('Club Robotix', 'h1');
        $resultat->assertSee('Karim Haddad');
        $resultat->assertSee('Programmation des robots');
        $resultat->assertSee('Atelier famille Poppy');
        $resultat->assertSee('9 membres');
    }

    public function testLeClubEstDansLeMenu(): void
    {
        $this->assertMatchesRegularExpression('#href="[^"]*/club">Club</a>#', $this->get('/')->getBody());
    }

    // ---------- Page joueur ----------

    public function testEspaceMembreReserveAuxMembres(): void
    {
        $this->assertStringContainsString('compte/connexion', $this->get('club/membres')->getRedirectUrl());
        $this->withSession($this->sessionDe('jules.moreau@exemple.fr'))->get('club/membres')
            ->assertSessionHas('erreur', 'Cet espace est réservé aux membres du Club Robotix.');
    }

    public function testEspaceMembreListeJoueursEtEvenements(): void
    {
        $resultat = $this->withSession($this->sessionDe('client@robotix.test'))->get('club/membres');

        $resultat->assertOK();
        $resultat->assertSee('Les joueurs du club', 'h2');
        $resultat->assertSee('Lefebvre');
        $resultat->assertSee('confirmé');
        $resultat->assertSee('Atelier famille Poppy');
        $resultat->assertSee('Atelier découverte Poppy'); // mes événements suivis
        $resultat->assertSee('A programmé un salut de la tête en Python.');
        $resultat->assertSee('Inscrit');
        $resultat->assertSee('S\'inscrire');
    }

    public function testInscriptionAUnEvenement(): void
    {
        $camille   = $this->idUtilisateur('client@robotix.test');
        $evenement = $this->idEvenement('Démonstration AgiBot X2');

        $resultat = $this->withSession($this->sessionDe('client@robotix.test'))->post('club/inscription', ['idEvenement' => $evenement]);

        $resultat->assertSessionHas('succes');
        $this->assertSame(1, $this->db->table('Inscription')->where(['idMembre' => $camille, 'idEvenement' => $evenement])->countAllResults());
        $this->assertSame(1, $this->db->table('MailAEnvoyer')->where('destinataire', 'client@robotix.test')->like('objet', 'Démonstration AgiBot X2')->countAllResults());
    }

    public function testDoubleInscriptionRefuseeProprement(): void
    {
        $this->withSession($this->sessionDe('client@robotix.test'))
            ->post('club/inscription', ['idEvenement' => $this->idEvenement('Atelier programmation Poppy')])
            ->assertSessionHas('erreur', 'Vous êtes déjà inscrit à cet événement.');
    }

    // ---------- Page entraîneur ----------

    public function testEspaceAnimateurReserveAuxAnimateurs(): void
    {
        $this->withSession($this->sessionDe('client@robotix.test'))->get('club/animateur')
            ->assertSessionHas('erreur', 'Cet espace est réservé aux animateurs du Club Robotix.');
    }

    public function testEspaceAnimateurAvecSesInscrits(): void
    {
        $resultat = $this->withSession($this->sessionDe('karim.haddad@robotix.test'))->get('club/animateur');

        $resultat->assertOK();
        $resultat->assertSee('Atelier découverte Poppy');
        $resultat->assertSee('Leroy');
        $resultat->assertSee('name="present[');
        $resultat->assertSee('Enregistrer les présences');
    }

    public function testPointageDesPresencesEtDuTravail(): void
    {
        $evenement = $this->idEvenement('Atelier programmation Poppy'); // animé par Karim
        $camille   = $this->idUtilisateur('client@robotix.test');
        $manon     = $this->idUtilisateur('manon.leroy@exemple.fr');

        $resultat = $this->withSession($this->sessionDe('karim.haddad@robotix.test'))
            ->post("club/animateur/evenements/{$evenement}/presences", [
                'present' => [$camille => '1', $manon => '0'],
                'travail' => [$camille => 'A programmé une danse.', $manon => ''],
            ]);

        $resultat->assertSessionHas('succes');
        $camilleLigne = $this->db->table('Inscription')->where(['idMembre' => $camille, 'idEvenement' => $evenement])->get()->getRowArray();
        $this->assertSame([1, 'A programmé une danse.'], [(int) $camilleLigne['present'], $camilleLigne['travailRealise']]);
        $this->assertSame(0, (int) $this->db->table('Inscription')->where(['idMembre' => $manon, 'idEvenement' => $evenement])->get()->getRow('present'));
    }

    public function testUnAnimateurNePointePasLesEvenementsDesAutres(): void
    {
        $evenement = $this->idEvenement('Atelier programmation Poppy'); // Karim, remplacé par Inès, pas par Théo

        $this->withSession($this->sessionDe('theo.garnier@robotix.test'))
            ->post("club/animateur/evenements/{$evenement}/presences", ['present' => [], 'travail' => []])
            ->assertSessionHas('erreur', 'Vous n\'animez pas cet événement.');
    }
}
