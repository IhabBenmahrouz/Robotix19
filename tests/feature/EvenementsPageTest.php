<?php

use Tests\Support\RobotixTestCase;

final class EvenementsPageTest extends RobotixTestCase
{
    public function testLaPageContientLeCalendrierEtSesFiltres(): void
    {
        $resultat = $this->get('evenements');

        $resultat->assertOK();
        $resultat->assertSee('id="calendrier"');
        $resultat->assertSee('api/evenements');
        $resultat->assertSee('id="filtre-type"');
        $resultat->assertSee('Démonstration');
        $resultat->assertSee('Robotix Paris Opéra');
        $resultat->assertSee('js/calendrier.js');
    }

    public function testLeMoisDemandeEstTransmisAuCalendrier(): void
    {
        $this->get('evenements', ['mois' => '2031-12'])->assertSee('data-mois="2031-12"');
    }

    public function testUnMoisInvalideEstIgnore(): void
    {
        $this->get('evenements', ['mois' => '<script>'])->assertSee('data-mois="' . date('Y-m') . '"');
    }
}
