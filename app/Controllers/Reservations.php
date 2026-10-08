<?php

namespace App\Controllers;

use App\Libraries\PanierReservations;
use CodeIgniter\HTTP\RedirectResponse;
use DomainException;
use InvalidArgumentException;

/**
 * Réservation de places pour les événements : liste, panier, enregistrement.
 */
class Reservations extends BaseController
{
    private const REFUS_ROLE = 'La réservation de places est réservée aux adhérents du Club.';

    public function index(): string
    {
        $panier = new PanierReservations();

        return view('reservations/liste', [
            'titre'      => 'Réserver des places',
            'possibles'  => $panier->chargerReservationsPossibles(),
            'nbPanier'   => $panier->nombreDePlaces(),
            'estClient'  => session('role') === 'client',
        ]);
    }

    public function ajouter(): RedirectResponse
    {
        if (session('role') !== 'client') {
            return redirect()->to(site_url('reservations'))->with('erreur', self::REFUS_ROLE);
        }

        try {
            $reservation = (new PanierReservations())->ajouter(
                (int) $this->request->getPost('idEvenement'),
                (int) $this->request->getPost('nbPlace'),
            );
        } catch (InvalidArgumentException $refus) {
            return redirect()->to(site_url('reservations'))->with('erreur', $refus->getMessage());
        }

        return redirect()->to(site_url('reservations/panier'))
            ->with('succes', 'Panier : ' . $reservation->getNbPlace() . ' place(s) pour « ' . $reservation->getNomEvenement() . ' ».');
    }

    public function panier(): string
    {
        $panier = new PanierReservations();

        return view('reservations/panier', [
            'titre'        => 'Mon panier',
            'reservations' => $panier->lister(),
            'nbPlaces'     => $panier->nombreDePlaces(),
        ]);
    }

    public function enregistrer(): RedirectResponse
    {
        if (session('role') !== 'client') {
            return redirect()->to(site_url('reservations'))->with('erreur', self::REFUS_ROLE);
        }

        try {
            $nombre = (new PanierReservations())->enregistrer((int) session('idUtilisateur'));
        } catch (DomainException $refus) {
            return redirect()->to(site_url('reservations/panier'))->with('erreur', $refus->getMessage());
        }

        if ($nombre === 0) {
            return redirect()->to(site_url('reservations'))->with('erreur', 'Votre panier est vide.');
        }

        return redirect()->to(site_url('compte/profil'))->with('succes', "Réservation enregistrée pour {$nombre} événement(s). À bientôt chez Robotix !");
    }

    public function vider(): RedirectResponse
    {
        (new PanierReservations())->vider();

        return redirect()->to(site_url('reservations'))->with('succes', 'Panier vidé.');
    }
}
