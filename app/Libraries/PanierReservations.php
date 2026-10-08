<?php

namespace App\Libraries;

use CodeIgniter\Session\SessionInterface;
use DomainException;
use InvalidArgumentException;

/**
 * Panier virtuel de réservations de places (AP2 — programmation objet).
 * - chargerReservationsPossibles() : le « TableauDeReservationPossible » (tableau d'objets Reservation) ;
 * - $panier : le « Panier » (tableau d'objets Reservation), conservé en session ;
 * - enregistrer() : du tableau d'objets vers la table Panier.
 */
class PanierReservations
{
    public const CLE_SESSION = 'panierReservations';

    private RobotixPdo $pdo;
    private SessionInterface $session;

    /** @var list<Reservation> */
    private array $panier;

    public function __construct(?RobotixPdo $pdo = null, ?SessionInterface $session = null)
    {
        $this->pdo     = $pdo ?? RobotixPdo::instance();
        $this->session = $session ?? session();
        $this->panier  = array_map([Reservation::class, 'depuisTableau'], $this->session->get(self::CLE_SESSION) ?? []);
    }

    /** @return list<Reservation> événements à venir avec leurs places restantes */
    public function chargerReservationsPossibles(): array
    {
        $lignes = $this->pdo->lignes(
            'SELECT e.idEvenement, e.titre, e.dateDebut, e.nbPlaces - ISNULL(SUM(p.nbPlace), 0) AS dispo
             FROM Evenement e
             LEFT JOIN Panier p ON p.idEvenement = e.idEvenement
             WHERE e.dateDebut > GETDATE()
             GROUP BY e.idEvenement, e.titre, e.dateDebut, e.nbPlaces
             ORDER BY e.dateDebut',
        );

        return array_map(
            static fn (array $l): Reservation => new Reservation((int) $l['idEvenement'], $l['titre'], (string) $l['dateDebut'], max(0, (int) $l['dispo'])),
            $lignes,
        );
    }

    public function ajouter(int $idEvenement, int $nbPlace): Reservation
    {
        if ($nbPlace < 1) {
            throw new InvalidArgumentException('Indiquez au moins 1 place.');
        }

        $possible = null;
        foreach ($this->chargerReservationsPossibles() as $reservation) {
            if ($reservation->getIdEvenement() === $idEvenement) {
                $possible = $reservation;
            }
        }
        if ($possible === null) {
            throw new InvalidArgumentException('Cet événement n\'est plus réservable.');
        }

        $existante = $this->trouver($idEvenement);
        $total     = ($existante?->getNbPlace() ?? 0) + $nbPlace;

        if ($existante === null) {
            $possible->setNbPlace($total);
            $this->panier[] = $possible;
            $resultat = $possible;
        } else {
            // On valide sur une copie pour laisser le panier intact en cas de refus
            $possible->setNbPlace($total);
            $existante->setNbPlaceDispo($possible->getNbPlaceDispo());
            $existante->setNbPlace($total);
            $resultat = $existante;
        }

        $this->sauver();

        return $resultat;
    }

    /** @return list<Reservation> */
    public function lister(): array
    {
        return $this->panier;
    }

    public function nombreDePlaces(): int
    {
        return array_sum(array_map(static fn (Reservation $r): int => $r->getNbPlace(), $this->panier));
    }

    public function estVide(): bool
    {
        return $this->panier === [];
    }

    /** Écrit le panier dans la table Panier ; les places sont revérifiées sous verrou. */
    public function enregistrer(int $idUtilisateur): int
    {
        if ($this->estVide()) {
            return 0;
        }

        $nombre = $this->pdo->transaction(function (RobotixPdo $pdo) use ($idUtilisateur): int {
            foreach ($this->panier as $reservation) {
                $dispo = (int) $pdo->valeur(
                    'SELECT e.nbPlaces - ISNULL((SELECT SUM(p.nbPlace) FROM Panier p WITH (UPDLOCK, HOLDLOCK)
                                                 WHERE p.idEvenement = :evenement1), 0)
                     FROM Evenement e WITH (UPDLOCK)
                     WHERE e.idEvenement = :evenement2 AND e.dateDebut > GETDATE()',
                    ['evenement1' => $reservation->getIdEvenement(), 'evenement2' => $reservation->getIdEvenement()],
                );

                if ($reservation->getNbPlace() > $dispo) {
                    throw new DomainException(sprintf(
                        'Plus assez de places pour « %s » : il en reste %d. Modifiez votre panier.',
                        $reservation->getNomEvenement(),
                        max(0, $dispo),
                    ));
                }

                $pdo->executer(
                    'INSERT INTO Panier (idEvenement, idUtilisateur, nomEvenement, nbPlace)
                     VALUES (:evenement, :utilisateur, :nom, :places)',
                    [
                        'evenement'   => $reservation->getIdEvenement(),
                        'utilisateur' => $idUtilisateur,
                        'nom'         => $reservation->getNomEvenement(),
                        'places'      => $reservation->getNbPlace(),
                    ],
                );
            }

            return count($this->panier);
        });

        foreach ($this->panier as $reservation) {
            $reservation->miseAJourNbPlaceDispo();
        }
        $this->vider();

        return $nombre;
    }

    public function vider(): void
    {
        $this->panier = [];
        $this->session->remove(self::CLE_SESSION);
    }

    private function trouver(int $idEvenement): ?Reservation
    {
        foreach ($this->panier as $reservation) {
            if ($reservation->getIdEvenement() === $idEvenement) {
                return $reservation;
            }
        }

        return null;
    }

    private function sauver(): void
    {
        $this->session->set(self::CLE_SESSION, array_map(static fn (Reservation $r): array => $r->versTableau(), $this->panier));
    }
}
