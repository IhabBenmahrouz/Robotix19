<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Réglages du jeu de démonstration (ex. : décalage en semaines du calendrier de démo).
 * Stockés en base, ils suivent les transactions comme les données qu'ils décrivent.
 */
class CreateParametreDemo extends Migration
{
    public function up(): void
    {
        $this->db->query('CREATE TABLE ParametreDemo (
            cle      VARCHAR(50)  NOT NULL PRIMARY KEY,
            valeur   VARCHAR(200) NOT NULL,
            dateMaj  DATETIME     NOT NULL DEFAULT GETDATE()
        )');
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE ParametreDemo');
    }
}
