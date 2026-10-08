<?php

use CodeIgniter\Test\CIUnitTestCase;

final class RobotixHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('robotix');
    }

    public function testEurosFormateALaFrancaise(): void
    {
        $this->assertSame('1 234,50 €', euros(1234.5));
        $this->assertSame('0,00 €', euros('0'));
    }

    public function testPrixTtcAppliqueLaTva(): void
    {
        $this->assertSame(16680.0, prix_ttc('13900.00', '20.00'));
    }

    public function testDateFrLitLeFormatSqlServer(): void
    {
        $this->assertSame('15/10/2026 14:00', date_fr('2026-10-15 14:00:00.000'));
        $this->assertSame('15/10/2026', date_fr('2026-10-15 14:00:00.000', false));
        $this->assertSame('', date_fr(null));
    }

    public function testDateSqlProduitUnFormatIsoNonAmbigu(): void
    {
        $this->assertSame('2026-11-25T14:30:00', date_sql('2026-11-25T14:30'));
    }

    public function testDateSaisiePourChampDatetimeLocal(): void
    {
        $this->assertSame('2026-11-25T14:30', date_saisie('2026-11-25 14:30:00.000'));
        $this->assertSame('', date_saisie(null));
    }

    public function testAgeEnAnneesRevolues(): void
    {
        $this->assertSame(18, age_le('2008-10-05', '2026-10-05')); // anniversaire le jour même
        $this->assertSame(17, age_le('2008-10-06', '2026-10-05')); // la veille de ses 18 ans
        $this->assertSame(32, age_le('1994-03-12', '2026-09-01'));
    }
}
