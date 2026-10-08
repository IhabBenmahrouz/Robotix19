<?php

use CodeIgniter\Exceptions\PageNotFoundException;
use Tests\Support\RobotixTestCase;

final class StoreTest extends RobotixTestCase
{
    public function testLeCatalogueAfficheLesDixRobots(): void
    {
        $resultat = $this->get('store');

        $resultat->assertOK();
        $resultat->assertSee('10 robots');
        $resultat->assertSee('Poppy Humanoid');
        $resultat->assertSee('XPeng Iron');
    }

    public function testFiltreParCategorie(): void
    {
        $idEducatif = (int) $this->db->table('Categorie')->where('libelle', 'Éducatif')->get()->getRow('idCategorie');

        $resultat = $this->get('store', ['categorie' => $idEducatif]);

        $resultat->assertSee('2 robots'); // Reachy 2 et Poppy Humanoid
        $resultat->assertSee('Reachy 2');
        $resultat->assertDontSee('XPeng Iron');
    }

    public function testTriParPrixCroissant(): void
    {
        $corps = $this->get('store', ['tri' => 'prix_asc'])->getBody();

        $this->assertLessThan(strpos($corps, 'XPeng Iron'), strpos($corps, 'Poppy Humanoid'));
    }

    public function testRechercheParNom(): void
    {
        $resultat = $this->get('store', ['q' => 'pepper']);

        $resultat->assertSee('1 robot');
        $resultat->assertDontSee('Ameca');
    }

    public function testFiltrePrixMaximumTtc(): void
    {
        $this->get('store', ['prixMax' => 20000])->assertSee('3 robots'); // Poppy (11 880 €), G1 (17 880 €), Pepper (19 080 €)
    }

    public function testFicheRobotAvecGalerieEtCompatibles(): void
    {
        $resultat = $this->get('store/robot/' . $this->idProduit('RBX-UNI-G1'));

        $resultat->assertOK();
        $resultat->assertSee('Unitree G1', 'h1');
        $resultat->assertSee('17 880,00 €');
        $resultat->assertSee('id="lightbox"');
        $resultat->assertSee('rbx-uni-g1-2.jpg');
        $resultat->assertSee('AgiBot X2'); // robot compatible
        $resultat->assertSee('usemap="#carte-robot"');
    }

    public function testRobotInexistantRenvoie404(): void
    {
        $this->assertPageIntrouvable('store/robot/999999');
    }

    public function testRobotInactifRenvoie404(): void
    {
        $id = $this->idProduit('RBX-UNI-G1');
        $this->db->table('Produit')->where('idProduit', $id)->update(['actif' => 0]);

        $this->assertPageIntrouvable('store/robot/' . $id);
    }

    private function assertPageIntrouvable(string $url): void
    {
        try {
            $this->get($url)->assertStatus(404);
        } catch (PageNotFoundException) {
            $this->addToAssertionCount(1);
        }
    }
}
