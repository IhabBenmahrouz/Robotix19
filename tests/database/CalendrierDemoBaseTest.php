<?php

use App\Libraries\CalendrierDemo;
use Tests\Support\RobotixTestCase;

final class CalendrierDemoBaseTest extends RobotixTestCase
{
    public function testLeDecalageEstMemoriseEnBase(): void
    {
        CalendrierDemo::memoriser(7);
        $this->assertSame(7, CalendrierDemo::courant()->semaines());

        CalendrierDemo::memoriser(3); // remplace la valeur précédente
        $this->assertSame(3, CalendrierDemo::courant()->semaines());
        $this->assertSame(1, $this->db->table('ParametreDemo')->countAllResults());
    }

    public function testLeSeederMemoriseSonDecalage(): void
    {
        putenv('ROBOTIX_DEMO_SEMAINES=5');
        try {
            \Config\Database::seeder()->setSilent(true)->call('RobotixSeeder');
        } finally {
            putenv('ROBOTIX_DEMO_SEMAINES');
        }

        $this->assertSame(5, CalendrierDemo::courant()->semaines());
        $debut = $this->db->table('Evenement')->select('dateDebut')->where('titre', 'Démonstration Unitree G1')->get()->getRow('dateDebut');
        $this->assertStringStartsWith('2026-11-12 14:00', $debut); // 8 octobre + 5 semaines, toujours un jeudi à 14 h
    }
}
