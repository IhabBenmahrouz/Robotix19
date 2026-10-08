<?php

use Tests\Support\RobotixTestCase;

final class AuthTest extends RobotixTestCase
{
    private function inscription(array $surcharge = []): array
    {
        $tarif    = (int) $this->db->table('Tarif')->where('code', 'passion')->get()->getRow('idTarif');
        $interets = array_column($this->db->table('Categorie')->select('idCategorie')->limit(2)->get()->getResultArray(), 'idCategorie');

        return $surcharge + [
            'nom' => 'Châtelet', 'prenom' => 'Hélène', 'email' => 'helene.chatelet@exemple.fr',
            'telephone' => '06 98 76 54 32', 'adresse' => '5 place Bellecour', 'codePostal' => '69002',
            'ville' => 'Lyon', 'motDePasse' => 'Robotix2026!', 'confirmation' => 'Robotix2026!',
            'dateNaissance' => '1990-05-12', 'idTarif' => (string) $tarif, 'interets' => $interets,
        ];
    }

    public function testLesPagesDeCompteSAffichent(): void
    {
        $this->get('compte/connexion')->assertSee('Connexion', 'h1');
        $inscription = $this->get('compte/inscription');
        $inscription->assertSee('data-valider');
        $inscription->assertSee('data-identique="motDePasse"');
        $inscription->assertSee('pattern="\d{5}"');
        $inscription->assertSee('enctype="multipart/form-data"');
        $inscription->assertSee('type="date"');
        $inscription->assertSee('type="radio" name="idTarif"');
        $inscription->assertSee('name="interets[]"');
        $inscription->assertSee('type="file"');
        $inscription->assertSee('id="donnees-adhesion"');
        $inscription->assertSee('Passion');
    }

    public function testInscriptionCreeUtilisateurClientEtAdresse(): void
    {
        $resultat = $this->post('compte/inscription', $this->inscription());

        $resultat->assertRedirect();
        $resultat->assertSessionHas('role', 'client');
        $id = $this->idUtilisateur('helene.chatelet@exemple.fr');
        $this->assertGreaterThan(0, $id);

        $utilisateur = $this->db->table('Utilisateur')->where('idUtilisateur', $id)->get()->getRowArray();
        $this->assertSame('Hélène', $utilisateur['prenom']); // accents conservés
        $this->assertTrue(password_verify('Robotix2026!', $utilisateur['motDePasse']));
        $this->assertSame(1, $this->db->table('Client')->where('idUtilisateur', $id)->countAllResults());
        $this->assertSame('Lyon', $this->db->table('Adresse')->where('idUtilisateur', $id)->get()->getRow('ville'));
    }

    public function testEmailDejaUtiliseRefuseMemeEnMajuscules(): void
    {
        $resultat = $this->post('compte/inscription', $this->inscription(['email' => 'CLIENT@Robotix.test']));

        $resultat->assertSessionHas('erreur');
        $resultat->assertSessionMissing('idUtilisateur');
    }

    public function testMotDePasseFaibleRefuse(): void
    {
        $this->post('compte/inscription', $this->inscription(['motDePasse' => 'robotix', 'confirmation' => 'robotix']))
            ->assertSessionHas('erreur');
        $this->assertSame(0, $this->idUtilisateur('helene.chatelet@exemple.fr'));
    }

    public function testConfirmationDifferenteRefusee(): void
    {
        $this->post('compte/inscription', $this->inscription(['confirmation' => 'Autre2026!']))
            ->assertSessionHas('erreur');
    }

    public function testConnexionAdmin(): void
    {
        $resultat = $this->post('compte/connexion', ['email' => 'admin@robotix.test', 'motDePasse' => 'Robotix2026!']);

        $resultat->assertRedirect();
        $resultat->assertSessionHas('role', 'admin');
        $resultat->assertSessionHas('prenom', 'Hugo');
    }

    public function testConnexionInsensibleALaCasseDeLEmail(): void
    {
        $this->post('compte/connexion', ['email' => ' Client@Robotix.TEST ', 'motDePasse' => 'Robotix2026!'])
            ->assertSessionHas('role', 'client');
    }

    public function testMauvaisMotDePasse(): void
    {
        $resultat = $this->post('compte/connexion', ['email' => 'admin@robotix.test', 'motDePasse' => 'mauvais']);

        $resultat->assertSessionHas('erreur');
        $resultat->assertSessionMissing('idUtilisateur');
    }

    public function testCompteDesactiveRefuse(): void
    {
        $this->db->table('Utilisateur')->where('email', 'client@robotix.test')->update(['actif' => 0]);

        $this->post('compte/connexion', ['email' => 'client@robotix.test', 'motDePasse' => 'Robotix2026!'])
            ->assertSessionMissing('idUtilisateur');
    }

    public function testDeconnexion(): void
    {
        $resultat = $this->withSession($this->sessionDe('client@robotix.test'))->post('compte/deconnexion');

        $resultat->assertRedirect();
        $resultat->assertSessionMissing('idUtilisateur');
    }

    public function testInscriptionEnregistreAdhesionEtInterets(): void
    {
        $this->post('compte/inscription', $this->inscription())->assertRedirect();

        $id = $this->idUtilisateur('helene.chatelet@exemple.fr');
        $this->assertEqualsWithDelta(99.0, (float) $this->db->table('Adhesion')->where('idUtilisateur', $id)->get()->getRow('montant'), 0.001);
        $this->assertSame(2, $this->db->table('ClientInteret')->where('idUtilisateur', $id)->countAllResults());
        $this->assertSame('1990-05-12', $this->db->table('Client')->where('idUtilisateur', $id)->get()->getRow('dateNaissance'));
    }

    public function testMineurRefuse(): void
    {
        $resultat = $this->post('compte/inscription', $this->inscription(['dateNaissance' => date('Y-m-d', strtotime('-17 years'))]));

        $resultat->assertSessionHas('erreur', 'Il faut avoir au moins 18 ans pour adhérer au Club Robotix.');
        $this->assertSame(0, $this->idUtilisateur('helene.chatelet@exemple.fr'));
    }

    public function testDixHuitAnsLeJourMemeAccepte(): void
    {
        $this->post('compte/inscription', $this->inscription(['dateNaissance' => date('Y-m-d', strtotime('-18 years'))]))
            ->assertSessionHas('role', 'client');
    }

    public function testFormuleInconnueRefusee(): void
    {
        $this->post('compte/inscription', $this->inscription(['idTarif' => '999999']))->assertSessionHas('erreur');
        $this->assertSame(0, $this->idUtilisateur('helene.chatelet@exemple.fr'));
    }
}
