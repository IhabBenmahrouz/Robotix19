<?php

use App\Libraries\LimiteurConnexion;
use Tests\Support\RobotixTestCase;

final class LimiteConnexionTest extends RobotixTestCase
{
    private function connecter(string $motDePasse, string $identifiant = 'client@robotix.test')
    {
        return $this->post('compte/connexion', ['identifiant' => $identifiant, 'motDePasse' => $motDePasse]);
    }

    /** Échecs anciens, insérés directement (il y a $minutes minutes). */
    private function echecsAnciens(int $nombre, int $minutes, string $identifiant = 'client@robotix.test', string $ip = '10.0.0.9'): void
    {
        for ($i = 0; $i < $nombre; $i++) {
            $this->db->query("INSERT INTO TentativeConnexion (identifiant, ip, reussie, dateTentative)
                VALUES (?, ?, 0, DATEADD(MINUTE, -$minutes, GETDATE()))", [$identifiant, $ip]);
        }
    }

    public function testChaqueEchecEstTrace(): void
    {
        $this->connecter('mauvais')->assertSessionHas('erreur', 'Identifiant ou mot de passe incorrect.');

        $this->assertSame(1, $this->db->table('TentativeConnexion')->where(['identifiant' => 'client@robotix.test', 'reussie' => 0])->countAllResults());
    }

    public function testAvertissementAvantLeBlocage(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $resultat = $this->connecter('mauvais');
        }

        $resultat->assertSessionHas('erreur', 'Identifiant ou mot de passe incorrect. Encore 2 essai(s) avant un blocage temporaire.');
    }

    public function testBloqueApresCinqEchecsMemeAvecLeBonMotDePasse(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $resultat = $this->connecter('mauvais');
        }
        $resultat->assertSessionHas('erreur', 'Identifiant ou mot de passe incorrect. Connexion bloquée pendant 15 minutes.');

        $this->connecter('Robotix2026!')->assertSessionHas('erreur', 'Trop de tentatives échouées. Réessayez dans 15 minute(s).');
        $this->assertNull(session('idUtilisateur'));
    }

    public function testLIdentifiantEstCompareSansCasse(): void
    {
        $this->echecsAnciens(5, 1, 'client@robotix.test');

        $this->connecter('Robotix2026!', ' Client@Robotix.TEST ')->assertSessionHas('erreur');
        $this->assertNull(session('idUtilisateur'));
    }

    public function testLeBlocageExpireAuBoutDeQuinzeMinutes(): void
    {
        $this->echecsAnciens(5, 16);

        $this->connecter('Robotix2026!')->assertRedirect();
        $this->assertSame($this->idUtilisateur('client@robotix.test'), session('idUtilisateur'));
    }

    public function testUneConnexionReussieRemetLeCompteurAZero(): void
    {
        $this->echecsAnciens(4, 5);
        $this->connecter('Robotix2026!');
        $this->assertSame($this->idUtilisateur('client@robotix.test'), session('idUtilisateur'));

        $this->assertSame(LimiteurConnexion::MAX_ECHECS, (new LimiteurConnexion())->essaisRestants('client@robotix.test'));
    }

    public function testLesAutresComptesNeSontPasBloques(): void
    {
        $this->echecsAnciens(5, 1, 'client@robotix.test');

        $this->connecter('Robotix2026!', 'admin@robotix.test');
        $this->assertSame($this->idUtilisateur('admin@robotix.test'), session('idUtilisateur'));
    }

    public function testUneAdresseIpQuiEssaieTropDeComptesEstBloquee(): void
    {
        $limiteur = new LimiteurConnexion();
        for ($i = 0; $i < LimiteurConnexion::MAX_ECHECS_IP; $i++) {
            $limiteur->noter("compte$i@exemple.fr", '203.0.113.7', false);
        }

        $this->assertGreaterThan(0, $limiteur->minutesDeBlocage('nouveau@exemple.fr', '203.0.113.7'));
        $this->assertSame(0, $limiteur->minutesDeBlocage('nouveau@exemple.fr', '198.51.100.1'));
    }
}
