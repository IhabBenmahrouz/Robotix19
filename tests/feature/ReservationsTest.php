<?php

use App\Libraries\PanierReservations;
use App\Libraries\Reservation;
use Tests\Support\RobotixTestCase;

final class ReservationsTest extends RobotixTestCase
{
    private function idEvenement(string $titre): int
    {
        return (int) $this->db->table('Evenement')->where('titre', $titre)->get()->getRow('idEvenement');
    }

    private function client(array $panier = []): array
    {
        return $this->sessionDe('client@robotix.test') + [PanierReservations::CLE_SESSION => $panier];
    }

    private function panierAvecAtelier(int $places): array
    {
        return [(new Reservation($this->idEvenement('Atelier famille Poppy'), 'Atelier famille Poppy', $this->demo('2026-12-09 18:30:00.000'), 12, $places))->versTableau()];
    }

    public function testVisiteurRedirigeVersLaConnexion(): void
    {
        $this->assertStringContainsString('compte/connexion', $this->get('reservations')->getRedirectUrl());
    }

    public function testListeDesEvenementsReservables(): void
    {
        $resultat = $this->withSession($this->client())->get('reservations');

        $resultat->assertOK();
        $resultat->assertSee('Atelier famille Poppy');
        $resultat->assertSee('Ajouter au panier');
        $resultat->assertSee('name="nbPlace"');
    }

    public function testAjoutAuPanier(): void
    {
        $resultat = $this->withSession($this->client())->post('reservations/ajouter', [
            'idEvenement' => $this->idEvenement('Atelier famille Poppy'), 'nbPlace' => '3',
        ]);

        $resultat->assertRedirect();
        $resultat->assertSessionHas('succes');
        $this->assertSame(3, $_SESSION[PanierReservations::CLE_SESSION][0]['nbPlace']);
    }

    public function testTropDePlacesRefuse(): void
    {
        $this->withSession($this->client())->post('reservations/ajouter', [
            'idEvenement' => $this->idEvenement('Atelier famille Poppy'), 'nbPlace' => '50',
        ])->assertSessionHas('erreur', 'Il ne reste que 12 place(s) pour « Atelier famille Poppy ».');
    }

    public function testUnAdministrateurNeReservePas(): void
    {
        $this->withSession($this->sessionDe('admin@robotix.test'))->post('reservations/ajouter', [
            'idEvenement' => $this->idEvenement('Atelier famille Poppy'), 'nbPlace' => '1',
        ])->assertSessionHas('erreur', 'La réservation de places est réservée aux adhérents du Club.');
    }

    public function testAffichageDuPanier(): void
    {
        $resultat = $this->withSession($this->client($this->panierAvecAtelier(4)))->get('reservations/panier');

        $resultat->assertSee('Atelier famille Poppy');
        $resultat->assertSee('4 place(s)');
        $resultat->assertSee('Enregistrer le panier');
        $resultat->assertSee('Continuer mes réservations');
    }

    public function testEnregistrementDuPanier(): void
    {
        $client = $this->idUtilisateur('client@robotix.test');

        $resultat = $this->withSession($this->client($this->panierAvecAtelier(2)))->post('reservations/enregistrer');

        $resultat->assertSessionHas('succes');
        $resultat->assertSessionMissing(PanierReservations::CLE_SESSION);
        $this->assertSame(1, $this->db->table('Panier')->where(['idUtilisateur' => $client, 'idEvenement' => $this->idEvenement('Atelier famille Poppy')])->countAllResults());
    }

    public function testViderLePanier(): void
    {
        $this->withSession($this->client($this->panierAvecAtelier(2)))->post('reservations/vider')
            ->assertSessionMissing(PanierReservations::CLE_SESSION);
    }

    public function testLaDeconnexionAnnuleLePanier(): void
    {
        $this->withSession($this->client($this->panierAvecAtelier(2)))->post('compte/deconnexion')
            ->assertSessionMissing(PanierReservations::CLE_SESSION);
    }
}
