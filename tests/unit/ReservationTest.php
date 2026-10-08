<?php

use App\Libraries\Reservation;
use CodeIgniter\Test\CIUnitTestCase;

final class ReservationTest extends CIUnitTestCase
{
    private function atelier(int $nbPlace = 0): Reservation
    {
        return new Reservation(7, 'Atelier famille Poppy', '2026-12-09 18:30:00.000', 80, $nbPlace);
    }

    public function testConstructeurEtAccesseurs(): void
    {
        $r = $this->atelier(2);

        $this->assertSame(7, $r->getIdEvenement());
        $this->assertSame('Atelier famille Poppy', $r->getNomEvenement());
        $this->assertSame('2026-12-09 18:30:00.000', $r->getDateResa());
        $this->assertSame(80, $r->getNbPlaceDispo());
        $this->assertSame(2, $r->getNbPlace());
    }

    public function testMutateurs(): void
    {
        $r = $this->atelier();
        $r->setNomEvenement('Atelier R1 (complet bientôt)');
        $r->setDateResa('2026-12-10 18:30:00.000');
        $r->setNbPlaceDispo(10);
        $r->setNbPlace(10);

        $this->assertSame(['Atelier R1 (complet bientôt)', '2026-12-10 18:30:00.000', 10, 10],
            [$r->getNomEvenement(), $r->getDateResa(), $r->getNbPlaceDispo(), $r->getNbPlace()]);
    }

    public function testMiseAJourDesPlacesDisponibles(): void
    {
        $r = $this->atelier(2);
        $r->miseAJourNbPlaceDispo();

        $this->assertSame(78, $r->getNbPlaceDispo());
    }

    public function testPlusDePlacesQueDisponiblesRefuse(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Il ne reste que 80 place(s) pour « Atelier famille Poppy ».');
        $this->atelier()->setNbPlace(81);
    }

    public function testAuMoinsUnePlace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->atelier()->setNbPlace(0);
    }

    public function testPlacesDisponiblesNegativesRefusees(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Reservation(1, 'Salon', '2026-12-12 10:00:00.000', -1);
    }

    public function testAllerRetourParUnTableau(): void
    {
        $copie = Reservation::depuisTableau($this->atelier(3)->versTableau());

        $this->assertEquals($this->atelier(3), $copie);
    }
}
