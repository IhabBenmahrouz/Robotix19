<?php

use Tests\Support\RobotixTestCase;

final class ShowroomsPageTest extends RobotixTestCase
{
    public function testLaCarteGoogleEstAfficheeSurLePremierShowroom(): void
    {
        $resultat = $this->get('showrooms');

        $resultat->assertOK();
        $resultat->assertSee('<iframe');
        $resultat->assertSee('data-src="https://maps.google.com/maps?q=45.7612,4.8562&amp;z=15&amp;output=embed"');
        $resultat->assertSee('data-cookies="autoriser-tiers"');
        $resultat->assertSee('id="btn-proche"');
        $resultat->assertSee('id="zoom-plus"');
        $resultat->assertSee('api/showrooms');
        $resultat->assertSee('js/carte.js');
    }

    public function testListeDeSecoursSansJavascript(): void
    {
        $this->get('showrooms')->assertSee('Robotix Marseille Vieux-Port');
    }
}
