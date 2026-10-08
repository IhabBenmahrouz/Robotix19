<?php

use Tests\Support\RobotixTestCase;

/**
 * Catalogue réel : vrais robots, vraies marques (siège, site officiel), vraies photos créditées.
 */
final class CatalogueReelTest extends RobotixTestCase
{
    private const ROBOTS = [
        'RBX-UNI-G1' => 'Unitree G1', 'RBX-FIG-02' => 'Figure 02', 'RBX-POL-R2' => 'Reachy 2',
        'RBX-ALD-PEPPER' => 'Pepper', 'RBX-XPG-IRON' => 'XPeng Iron', 'RBX-EA-AMECA' => 'Ameca',
        'RBX-AGB-X2' => 'AgiBot X2', 'RBX-PAL-ARI' => 'ARI', 'RBX-POP-HUM' => 'Poppy Humanoid', 'RBX-IIT-ICUB' => 'iCub',
    ];

    public function testLesDixRobotsReels(): void
    {
        $noms = array_column($this->db->table('Produit')->select('reference, nom')->get()->getResultArray(), 'nom', 'reference');
        ksort($noms);
        $attendus = self::ROBOTS;
        ksort($attendus);

        $this->assertSame($attendus, $noms);
    }

    public function testChaqueRobotAUnStatutCommercialReel(): void
    {
        $statuts = array_column($this->db->table('Produit')->select('reference, statutCommercial')->get()->getResultArray(), 'statutCommercial', 'reference');

        $this->assertSame('Commercialisé', $statuts['RBX-UNI-G1']);
        $this->assertSame('Production arrêtée', $statuts['RBX-ALD-PEPPER']);
        $this->assertSame('Plateforme de recherche', $statuts['RBX-IIT-ICUB']);
        $this->assertSame('Commercialisation prévue', $statuts['RBX-XPG-IRON']);
        $this->assertNotContains(null, $statuts);
    }

    public function testChaquePhotoEstRealleEtCreditee(): void
    {
        $images = $this->db->table('Image')->get()->getResultArray();

        $this->assertCount(15, $images);
        foreach ($images as $image) {
            $this->assertStringEndsWith('.jpg', $image['fichier']);
            $this->assertFileExists(FCPATH . 'images/robots/' . $image['fichier']);
            $this->assertNotEmpty($image['credit'], $image['fichier']);
            $this->assertMatchesRegularExpression('/^(CC0|CC BY(-SA)? \d\.\d)$/', $image['licence'], $image['fichier']);
            $this->assertStringStartsWith('https://commons.wikimedia.org/wiki/File:', $image['source']);
        }
    }

    public function testLesMarquesOntSiegeEtSiteOfficiel(): void
    {
        $marques = array_column($this->db->table('Marque')->get()->getResultArray(), null, 'nom');

        $this->assertSame('Hangzhou, Chine', $marques['Unitree Robotics']['siege']);
        $this->assertSame('https://www.unitree.com', $marques['Unitree Robotics']['siteWeb']);
        $this->assertSame('San Jose, Californie, États-Unis', $marques['Figure AI']['siege']);
        $this->assertSame('Bordeaux, France', $marques['Pollen Robotics']['siege']);
        $this->assertSame(2004, (int) $marques['PAL Robotics']['anneeCreation']);
        foreach ($marques as $nom => $marque) {
            $this->assertStringStartsWith('https://', $marque['siteWeb'], $nom);
            $this->assertNotEmpty($marque['siege'], $nom);
        }
    }

    public function testLaFicheAfficheStatutEtCreditPhoto(): void
    {
        $resultat = $this->get('store/robot/' . $this->idProduit('RBX-XPG-IRON'));

        $resultat->assertSee('Commercialisation prévue');
        $resultat->assertSee('Tim Wu');
        $resultat->assertSee('CC BY-SA 4.0');
        $resultat->assertSee('Guangzhou, Chine');
        $resultat->assertSee('https://www.xpeng.com');
    }

    public function testPageDesCreditsPhotos(): void
    {
        $resultat = $this->get('credits');

        $resultat->assertOK();
        $resultat->assertSee('Crédits photos', 'h1');
        $resultat->assertSee('Niccolò Caranti');
        $resultat->assertSee('Xavier Caré / Wikimedia Commons / CC-BY-SA');
        $this->assertSame(15, substr_count($resultat->getBody(), 'commons.wikimedia.org/wiki/File:'));
    }

    public function testLeBandeauDAccueilEstUneVraiePhoto(): void
    {
        $resultat = $this->get('/');

        $resultat->assertSee('images/robots/rbx-xpg-iron-1.jpg');
        $resultat->assertSee('Photo : Tim Wu');
    }
}
