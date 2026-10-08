<?php

use Tests\Support\RobotixTestCase;

final class CookiesTest extends RobotixTestCase
{
    public function testLeBandeauEstPresentSurToutesLesPages(): void
    {
        foreach (['/', 'store', 'contact'] as $page) {
            $resultat = $this->get($page);
            $resultat->assertSee('id="cookies"');
            $resultat->assertSee('js/consentement.js');
        }
    }

    public function testOngletsEtBoutonsDuBandeau(): void
    {
        $resultat = $this->get('/');

        foreach (['Consentement', 'Détails', 'À propos des cookies', 'Refuser', 'Personnaliser', 'Tout autoriser', 'Gérer les cookies'] as $texte) {
            $resultat->assertSee($texte);
        }
        $resultat->assertSee('data-cookies="tout"');
        $resultat->assertSee('id="cookie-tiers"');
    }
}
