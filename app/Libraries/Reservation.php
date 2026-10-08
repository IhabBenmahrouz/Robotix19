<?php

namespace App\Libraries;

use InvalidArgumentException;

/**
 * Réservation de places pour un événement Robotix (modèle objet du panier).
 */
class Reservation
{
    public function __construct(
        private int $idEvenement,
        private string $nomEvenement,
        private string $dateResa,
        private int $nbPlaceDispo,
        private int $nbPlace = 0,
    ) {
        $this->setNbPlaceDispo($nbPlaceDispo);

        if ($nbPlace !== 0) {
            $this->setNbPlace($nbPlace);
        }
    }

    public function getIdEvenement(): int
    {
        return $this->idEvenement;
    }

    public function getNomEvenement(): string
    {
        return $this->nomEvenement;
    }

    public function getDateResa(): string
    {
        return $this->dateResa;
    }

    public function getNbPlaceDispo(): int
    {
        return $this->nbPlaceDispo;
    }

    public function getNbPlace(): int
    {
        return $this->nbPlace;
    }

    public function setNomEvenement(string $nomEvenement): void
    {
        $this->nomEvenement = $nomEvenement;
    }

    public function setDateResa(string $dateResa): void
    {
        $this->dateResa = $dateResa;
    }

    public function setNbPlaceDispo(int $nbPlaceDispo): void
    {
        if ($nbPlaceDispo < 0) {
            throw new InvalidArgumentException('Le nombre de places disponibles ne peut pas être négatif.');
        }
        $this->nbPlaceDispo = $nbPlaceDispo;
    }

    public function setNbPlace(int $nbPlace): void
    {
        if ($nbPlace < 1) {
            throw new InvalidArgumentException('Indiquez au moins 1 place.');
        }
        if ($nbPlace > $this->nbPlaceDispo) {
            throw new InvalidArgumentException(sprintf('Il ne reste que %d place(s) pour « %s ».', $this->nbPlaceDispo, $this->nomEvenement));
        }
        $this->nbPlace = $nbPlace;
    }

    /** Après enregistrement : les places réservées ne sont plus disponibles. */
    public function miseAJourNbPlaceDispo(): void
    {
        $this->nbPlaceDispo -= $this->nbPlace;
    }

    public function versTableau(): array
    {
        return [
            'idEvenement'  => $this->idEvenement,
            'nomEvenement' => $this->nomEvenement,
            'dateResa'     => $this->dateResa,
            'nbPlaceDispo' => $this->nbPlaceDispo,
            'nbPlace'      => $this->nbPlace,
        ];
    }

    public static function depuisTableau(array $donnees): self
    {
        return new self(
            (int) $donnees['idEvenement'],
            (string) $donnees['nomEvenement'],
            (string) $donnees['dateResa'],
            (int) $donnees['nbPlaceDispo'],
            (int) $donnees['nbPlace'],
        );
    }
}
