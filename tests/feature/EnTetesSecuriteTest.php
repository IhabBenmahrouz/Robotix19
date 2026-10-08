<?php

use Tests\Support\RobotixTestCase;

final class EnTetesSecuriteTest extends RobotixTestCase
{
    public function testLesPagesEnvoientLesEnTetesDeSecurite(): void
    {
        foreach (['/', 'compte/connexion', 'api/showrooms'] as $page) {
            $resultat = $this->get($page);

            $resultat->assertHeader('X-Frame-Options', 'SAMEORIGIN');          // pas d'affichage dans le cadre d'un autre site (clickjacking)
            $resultat->assertHeader('X-Content-Type-Options', 'nosniff');      // le navigateur respecte le type annoncé
            $resultat->assertHeader('Referrer-Policy', 'same-origin');         // l'adresse des pages ne fuit pas vers les autres sites
        }
    }

    public function testLeDossierDesEnvoisInterditLesScripts(): void
    {
        $regles = (string) file_get_contents(FCPATH . 'uploads/.htaccess');

        $this->assertStringContainsString('Require all denied', $regles);
        $this->assertMatchesRegularExpression('/php/', $regles);
    }
}
