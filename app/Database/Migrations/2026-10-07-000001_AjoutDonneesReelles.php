<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Catalogue réel : siège et année de création des marques, statut commercial réel des robots,
 * crédits des photos (auteur, licence, page source Wikimedia Commons).
 */
class AjoutDonneesReelles extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE Marque ADD siege VARCHAR(120) NULL, anneeCreation INT NULL');
        $this->db->query('ALTER TABLE Produit ADD statutCommercial VARCHAR(40) NULL');
        $this->db->query('ALTER TABLE Image ADD credit VARCHAR(200) NULL, licence VARCHAR(30) NULL, source VARCHAR(300) NULL');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE Image DROP COLUMN credit, licence, source');
        $this->db->query('ALTER TABLE Produit DROP COLUMN statutCommercial');
        $this->db->query('ALTER TABLE Marque DROP COLUMN siege, anneeCreation');
    }
}
