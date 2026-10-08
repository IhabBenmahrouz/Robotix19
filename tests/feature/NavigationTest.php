<?php

use Tests\Support\RobotixTestCase;

final class NavigationTest extends RobotixTestCase
{
    public function testAccueilUtiliseLeGabarit(): void
    {
        $resultat = $this->get('/');

        $resultat->assertOK();
        $resultat->assertSee('ROBOTIX');
        $resultat->assertSee('css/robotix.css');
        $resultat->assertSee('name="viewport"');
        $resultat->assertSee('Notre activité', 'h2');
    }

    public function testLeMenuContientToutesLesRubriques(): void
    {
        $resultat = $this->get('/');

        foreach (['Accueil', 'Store', 'News', 'Tarifs', 'Événements', 'Showrooms', 'Contact', 'Compte'] as $rubrique) {
            $resultat->assertSee($rubrique);
        }
    }

    public function testLaRubriqueCouranteEstMiseEnEvidence(): void
    {
        $corps = $this->get('news')->getBody();

        $this->assertMatchesRegularExpression('#class="nav-link active" aria-current="page" href="[^"]*/news"#', $corps);
        $this->assertSame(1, substr_count($corps, 'aria-current="page"'));
    }

    public function testPageNewsEnPreparation(): void
    {
        $this->get('news')->assertSee('Robotix News', 'h1');
    }
}
