<?php

namespace App\Libraries;

/**
 * Trace les actions d'administration dans la table Journal.
 */
final class Journaliseur
{
    public static function noter(string $typeAction, string $tableCible, int $idCible, string $description): void
    {
        $idUtilisateur = (int) session('idUtilisateur');

        if ($idUtilisateur === 0) {
            return;
        }

        db_connect()->table('Journal')->insert([
            'typeAction'    => mb_substr($typeAction, 0, 20),
            'tableCible'    => $tableCible,
            'idCible'       => $idCible,
            'description'   => mb_substr($description, 0, 500),
            'idUtilisateur' => $idUtilisateur,
        ]);
    }
}
