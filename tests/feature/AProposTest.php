<?php

use Tests\Support\RobotixTestCase;

final class AProposTest extends RobotixTestCase
{
    public function testPagePubliqueAvecLesChiffresDeLaBase(): void
    {
        $resultat = $this->get('a-propos');

        $resultat->assertOK();
        $resultat->assertSee('À propos du projet');
        $resultat->assertSee('<p class="stat__valeur mb-0">10</p>'); // 10 robots et 10 marques réels
        $resultat->assertSee('<p class="stat__valeur mb-0">5</p>');  // 5 procédures ps_*
        $resultat->assertSee('procédures stockées');
        $resultat->assertSee('karim.h');
    }

    public function testLesDatesDuParcoursSuiventLeCalendrierDeDemo(): void
    {
        $this->get('a-propos')->assertSee('réunion du ' . $this->demoFr('2026-09-30'));
    }
}
