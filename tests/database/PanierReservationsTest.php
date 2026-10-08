<?php

use App\Libraries\PanierReservations;
use App\Libraries\Reservation;
use Tests\Support\RobotixTestCase;

final class PanierReservationsTest extends RobotixTestCase
{
    private const ATELIER_R1 = 'Atelier famille Poppy';       // 12 places, aucune réservée
    private const DEMO_FIGURE = 'Démonstration Figure 02';          // 30 places

    private function idEvenement(string $titre): int
    {
        return (int) $this->pdo->valeur('SELECT idEvenement FROM Evenement WHERE titre = :titre', ['titre' => $titre]);
    }

    private function panier(): PanierReservations
    {
        return new PanierReservations($this->pdo, session());
    }

    protected function setUp(): void
    {
        parent::setUp();
        session()->remove(PanierReservations::CLE_SESSION);
    }

    public function testTableauDesReservationsPossibles(): void
    {
        $possibles = $this->panier()->chargerReservationsPossibles();

        $this->assertContainsOnlyInstancesOf(Reservation::class, $possibles);
        $parNom = [];
        foreach ($possibles as $r) {
            $parNom[$r->getNomEvenement()] = $r->getNbPlaceDispo();
        }
        $this->assertSame(12, $parNom[self::ATELIER_R1]);
        $this->assertSame(30, $parNom[self::DEMO_FIGURE]);
    }

    public function testAjoutCumuleLeMemeEvenement(): void
    {
        $panier = $this->panier();
        $panier->ajouter($this->idEvenement(self::ATELIER_R1), 2);
        $panier->ajouter($this->idEvenement(self::ATELIER_R1), 3);

        $this->assertCount(1, $panier->lister());
        $this->assertSame(5, $panier->nombreDePlaces());
    }

    public function testLePanierEstConserveEnSession(): void
    {
        $this->panier()->ajouter($this->idEvenement(self::DEMO_FIGURE), 4);

        $relu = $this->panier()->lister();
        $this->assertCount(1, $relu);
        $this->assertSame(4, $relu[0]->getNbPlace());
    }

    public function testTropDePlacesRefuse(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Il ne reste que 12 place(s) pour « Atelier famille Poppy ».');
        $this->panier()->ajouter($this->idEvenement(self::ATELIER_R1), 13);
    }

    public function testLeCumulNePeutDepasserLesPlacesRestantes(): void
    {
        $panier = $this->panier();
        $panier->ajouter($this->idEvenement(self::ATELIER_R1), 10);

        try {
            $panier->ajouter($this->idEvenement(self::ATELIER_R1), 3);
            $this->fail('InvalidArgumentException attendue');
        } catch (InvalidArgumentException) {
        }

        $this->assertSame(10, $panier->nombreDePlaces());
    }

    public function testEvenementPasseNonReservable(): void
    {
        $id = $this->idEvenement(self::ATELIER_R1);
        $this->pdo->executer("UPDATE Evenement SET dateDebut = '2020-01-01T10:00:00', dateFin = '2020-01-01T12:00:00' WHERE idEvenement = :id", ['id' => $id]);

        $this->expectExceptionMessage('Cet événement n\'est plus réservable.');
        $this->panier()->ajouter($id, 1);
    }

    public function testEnregistrementDuPanierEnTable(): void
    {
        $client = $this->idUtilisateur('client@robotix.test');
        $panier = $this->panier();
        $panier->ajouter($this->idEvenement(self::ATELIER_R1), 2);
        $panier->ajouter($this->idEvenement(self::DEMO_FIGURE), 1);

        $this->assertSame(2, $panier->enregistrer($client));
        $this->assertTrue($panier->estVide());
        $this->assertSame(2, (int) $this->pdo->valeur(
            'SELECT nbPlace FROM Panier WHERE idUtilisateur = :client AND idEvenement = :evenement',
            ['client' => $client, 'evenement' => $this->idEvenement(self::ATELIER_R1)],
        ));
    }

    public function testPlacesPrisesEntreTempsRienNEstEcrit(): void
    {
        $client = $this->idUtilisateur('client@robotix.test');
        $autre  = $this->idUtilisateur('emma.petit@exemple.fr');
        $atelier = $this->idEvenement(self::ATELIER_R1);
        $panier = $this->panier();
        $panier->ajouter($this->idEvenement(self::DEMO_FIGURE), 1);
        $panier->ajouter($atelier, 10);

        // Un autre adhérent réserve 5 places avant l'enregistrement
        $this->pdo->executer(
            "INSERT INTO Panier (idEvenement, idUtilisateur, nomEvenement, nbPlace) VALUES (:evenement, :client, 'Atelier', 5)",
            ['evenement' => $atelier, 'client' => $autre],
        );

        try {
            $panier->enregistrer($client);
            $this->fail('DomainException attendue');
        } catch (DomainException $refus) {
            $this->assertStringContainsString('il en reste 7', $refus->getMessage());
        }

        $this->assertSame(0, (int) $this->pdo->valeur('SELECT COUNT(*) FROM Panier WHERE idUtilisateur = :client AND dateResa > :limite', ['client' => $client, 'limite' => str_replace('-', '', $this->demo('2026-10-02'))])); // AAAAMMJJ : non ambigu pour SQL Server
        $this->assertFalse($panier->estVide());
    }

    public function testViderLePanier(): void
    {
        $panier = $this->panier();
        $panier->ajouter($this->idEvenement(self::DEMO_FIGURE), 2);
        $panier->vider();

        $this->assertTrue($this->panier()->estVide());
    }
}
