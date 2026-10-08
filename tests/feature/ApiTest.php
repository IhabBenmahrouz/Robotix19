<?php

use Tests\Support\RobotixTestCase;

final class ApiTest extends RobotixTestCase
{
    /** Événements du mois (du calendrier de démo) qui contient la date donnée, indexés par titre. */
    private function evenementsDuMoisDe(string $date): array
    {
        $resultat = $this->get('api/evenements', ['mois' => substr($this->demo($date), 0, 7)]);
        $resultat->assertOK();

        return array_column(json_decode($resultat->getJSON(), true), null, 'titre');
    }

    public function testEvenementsDuMois(): void
    {
        $evenements = $this->evenementsDuMoisDe('2026-10-08');
        $mois       = substr($this->demo('2026-10-08'), 0, 7);

        foreach ($evenements as $evenement) {
            $this->assertStringStartsWith($mois, $evenement['debut']); // seulement le mois demandé
        }
        $g1 = $evenements['Démonstration Unitree G1'];
        $this->assertSame($this->demo('2026-10-08T14:00:00'), $g1['debut']);
        $this->assertSame('Démonstration', $g1['typeLibelle']);
        $this->assertIsInt($g1['idShowroom']);
        $this->assertStringContainsString('store/robot/', $g1['urlProduit']);
    }

    public function testEvenementHorsShowroom(): void
    {
        $salon = $this->evenementsDuMoisDe('2026-10-24')['Salon des robots humanoïdes — Lyon Eurexpo'];

        $this->assertNull($salon['idShowroom']);
        $this->assertNull($salon['urlProduit']);
        $this->assertSame($this->demo('2026-10-25T19:00:00'), $salon['fin']);
    }

    public function testMoisInvalideRenvoie400(): void
    {
        foreach (['2026-13', 'octobre', '2026-1', "2026-10' OR 1=1"] as $mois) {
            $this->get('api/evenements', ['mois' => $mois])->assertStatus(400);
        }
    }

    public function testSansMoisRenvoieLeMoisCourant(): void
    {
        $this->get('api/evenements')->assertOK();
    }

    public function testShowroomsAvecCoordonneesNumeriques(): void
    {
        $showrooms = json_decode($this->get('api/showrooms')->getJSON(), true);

        $this->assertCount(3, $showrooms);
        $this->assertSame('Lyon', $showrooms[0]['ville']);
        $this->assertIsFloat($showrooms[0]['latitude']);
        $this->assertEqualsWithDelta(45.7612, $showrooms[0]['latitude'], 0.0001);
    }
}
