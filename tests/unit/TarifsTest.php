<?php

use App\Libraries\Tarifs;
use CodeIgniter\Test\CIUnitTestCase;

final class TarifsTest extends CIUnitTestCase
{
    public function testLesDureesDeFinancement(): void
    {
        $this->assertSame([1, 12, 24, 36], array_column(Tarifs::financements(), 'mois'));
    }

    public function testLesServicesNeSontPlusEcritsEnDur(): void
    {
        $this->assertFalse(method_exists(Tarifs::class, 'options'));
    }
}
