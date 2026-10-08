<?php

use App\Libraries\CalendrierDemo;
use CodeIgniter\Test\CIUnitTestCase;

final class CalendrierDemoTest extends CIUnitTestCase
{
    public function testSansDecalageLesDatesSontInchangees(): void
    {
        $calendrier = new CalendrierDemo(0);

        $this->assertSame('2026-10-08T14:00:00', $calendrier->date('2026-10-08T14:00:00'));
        $this->assertSame('1994-03-12', $calendrier->date('1994-03-12'));
        $this->assertSame(2026, $calendrier->annee(2026));
    }

    public function testDecalageEnSemainesConserveJourEtHeure(): void
    {
        $calendrier = new CalendrierDemo(52);

        $this->assertSame('2027-10-07T14:00:00', $calendrier->date('2026-10-08T14:00:00')); // toujours un jeudi, 14 h
        $this->assertSame('2027-09-21', $calendrier->date('2026-09-22'));
        $this->assertSame(2027, $calendrier->annee(2026));
        $this->assertSame(2026, $calendrier->annee(2025));
        $this->assertSame('2027-11-24T14:00', $calendrier->date('2026-11-25T14:00'));
        $this->assertSame('2027-12-08 18:30:00.000', $calendrier->date('2026-12-09 18:30:00.000'));
    }

    public function testLeDecalageSuitLaDateDuJour(): void
    {
        $this->assertSame(0, CalendrierDemo::semainesDepuisReference('2026-10-07'));
        $this->assertSame(0, CalendrierDemo::semainesDepuisReference('2026-09-01')); // jamais de décalage négatif
        $this->assertSame(1, CalendrierDemo::semainesDepuisReference('2026-10-12'));
        $this->assertSame(52, CalendrierDemo::semainesDepuisReference('2027-10-04'));
    }
}
