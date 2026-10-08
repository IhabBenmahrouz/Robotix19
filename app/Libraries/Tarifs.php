<?php

namespace App\Libraries;

/**
 * Paramètres de calcul des prix qui ne dépendent pas de la base :
 * TVA et durées de financement. Les services sont dans la table Tarif.
 */
final class Tarifs
{
    public const TAUX_TVA = 20.0;

    public static function financements(): array
    {
        return [
            ['mois' => 1, 'taux' => 0.0, 'libelle' => 'Comptant'],
            ['mois' => 12, 'taux' => 0.0, 'libelle' => '12 mois sans frais'],
            ['mois' => 24, 'taux' => 3.9, 'libelle' => '24 mois (taux annuel 3,9 %)'],
            ['mois' => 36, 'taux' => 5.9, 'libelle' => '36 mois (taux annuel 5,9 %)'],
        ];
    }
}
