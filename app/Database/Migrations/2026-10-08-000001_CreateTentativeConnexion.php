<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tentatives de connexion : sert à bloquer temporairement les essais répétés (force brute).
 */
class CreateTentativeConnexion extends Migration
{
    public function up(): void
    {
        $this->db->query('CREATE TABLE TentativeConnexion (
            idTentative   INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_TentativeConnexion PRIMARY KEY,
            identifiant   VARCHAR(150) NOT NULL,
            ip            VARCHAR(45)  NOT NULL,
            reussie       BIT          NOT NULL,
            dateTentative DATETIME     NOT NULL CONSTRAINT DF_TentativeConnexion_date DEFAULT GETDATE()
        )');
        $this->db->query('CREATE INDEX IX_TentativeConnexion_identifiant ON TentativeConnexion (identifiant, dateTentative)');
        $this->db->query('CREATE INDEX IX_TentativeConnexion_ip ON TentativeConnexion (ip, dateTentative)');
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE TentativeConnexion');
    }
}
