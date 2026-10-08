<?php

use Tests\Support\RobotixTestCase;

final class AccueilTest extends RobotixTestCase
{
    public function testLesRobotsPharesSontLesPlusHautDeGamme(): void
    {
        $resultat = $this->get('/');

        $resultat->assertSee('Robots phares', 'h2');
        $resultat->assertSee('iCub');
        $resultat->assertSee('275 880,00 €'); // 229 900 € HT + 20 % de TVA
        $resultat->assertDontSee('Poppy Humanoid'); // le moins cher n'est pas un robot phare
    }

    public function testImageReactiveAvecSesZones(): void
    {
        $resultat = $this->get('/');

        $resultat->assertSee('usemap="#carte-robot"');
        $resultat->assertSee('<map name="carte-robot">');
        $this->assertSame(5, substr_count($resultat->getBody(), '<area '));
        $resultat->assertSee('id="modale-zone"');
        $resultat->assertSee('js/image-map.js');
    }

    public function testSectionProchainsEvenements(): void
    {
        $this->get('/')->assertSee('Prochains événements', 'h2');
    }
}
