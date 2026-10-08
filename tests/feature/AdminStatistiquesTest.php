<?php

use Tests\Support\RobotixTestCase;

final class AdminStatistiquesTest extends RobotixTestCase
{
    public function testPageDesStatistiques(): void
    {
        $resultat = $this->withSession($this->sessionDe('admin@robotix.test'))->get('admin/statistiques', ['annee' => $this->anneeDemo(2026)]);

        $resultat->assertOK();
        $resultat->assertSee('985,75 €');
        $resultat->assertSee('40,0 %');
        $resultat->assertSee('<option value="' . $this->anneeDemo(2025) . '">' . $this->anneeDemo(2025) . '</option>');
    }

    public function testAnneeInvalideRemplaceeParLAnneeEnCours(): void
    {
        $this->withSession($this->sessionDe('admin@robotix.test'))->get('admin/statistiques', ['annee' => 'abc'])
            ->assertSee('<option value="' . date('Y') . '" selected>');
    }

    public function testReserveAuxAdministrateurs(): void
    {
        $this->withSession($this->sessionDe('client@robotix.test'))->get('admin/statistiques')->assertRedirect();
    }
}
