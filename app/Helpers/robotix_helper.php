<?php

/**
 * Fonctions utilitaires Robotix : prix, dates et menu.
 */

if (! function_exists('euros')) {
    /** 1234.5 → « 1 234,50 € » */
    function euros(float|string $montant): string
    {
        return number_format((float) $montant, 2, ',', ' ') . ' €';
    }
}

if (! function_exists('prix_ttc')) {
    /** Prix TTC arrondi au centime ; $taux est un pourcentage (20 = 20 %). */
    function prix_ttc(float|string $ht, float|string $taux): float
    {
        return round((float) $ht * (1 + (float) $taux / 100), 2);
    }
}

if (! function_exists('date_fr')) {
    /** « 2026-10-15 14:00:00.000 » (format SQL Server) → « 15/10/2026 14:00 » */
    function date_fr(?string $date, bool $heure = true): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        return date($heure ? 'd/m/Y H:i' : 'd/m/Y', strtotime(substr($date, 0, 19)));
    }
}

if (! function_exists('date_sql')) {
    /**
     * Saisie (« 2026-11-25T14:30 » ou « 2026-11-25 14:30:00 ») → « 2026-11-25T14:30:00 ».
     * Le format ISO avec « T » est compris par SQL Server quelle que soit sa langue.
     */
    function date_sql(string $saisie): string
    {
        return date('Y-m-d\TH:i:s', strtotime($saisie));
    }
}

if (! function_exists('date_saisie')) {
    /** Date de la base → valeur d'un champ <input type="datetime-local">. */
    function date_saisie(?string $date): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        return date('Y-m-d\TH:i', strtotime(substr($date, 0, 19)));
    }
}

if (! function_exists('menu_actif')) {
    /** Vrai si le premier segment de l'URL courante vaut $segment ('' = accueil). */
    function menu_actif(string $segment): bool
    {
        return service('request')->getUri()->getSegment(1) === $segment;
    }
}

if (! function_exists('age_le')) {
    /** Âge en années révolues à la date de référence (aujourd'hui par défaut). */
    function age_le(string $dateNaissance, ?string $reference = null): int
    {
        return (new DateTimeImmutable($dateNaissance))->diff(new DateTimeImmutable($reference ?? 'today'))->y;
    }
}
