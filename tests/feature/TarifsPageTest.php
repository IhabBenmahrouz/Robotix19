<?php

use Tests\Support\RobotixTestCase;

final class TarifsPageTest extends RobotixTestCase
{
    public function testPresentationDesGammesEtServices(): void
    {
        $resultat = $this->get('tarifs');

        $resultat->assertOK();
        $resultat->assertSee('Nos gammes', 'h2');
        $resultat->assertSee('Compagnon');
        $resultat->assertSee('Premium');
        $resultat->assertSee('Garantie étendue 3 ans');
        $resultat->assertSee('11 880,00 €'); // Poppy Humanoid TTC : gamme Éducatif « à partir de »
    }

    public function testLeCalculateurEstPresentAvecSesDonnees(): void
    {
        $resultat = $this->get('tarifs');

        $resultat->assertSee('id="calculateur"');
        $resultat->assertSee('id="donnees-tarifs"');
        $resultat->assertSee('js/calculateur.js');
    }

    public function testLeRobotPasseEnParametreEstPreselectionne(): void
    {
        $id = $this->idProduit('RBX-FIG-02');

        $this->get('tarifs', ['robot' => $id])->assertSee('<option value="' . $id . '" selected>');
    }
}
