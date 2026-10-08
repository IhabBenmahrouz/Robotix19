<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Ajoute à Robotix58 les tables nécessaires à la grille AP1 :
 * showrooms (Google Maps), événements (calendrier) et messages de contact.
 */
class CreateShowroomEvenementContact extends Migration
{
    public function up(): void
    {
        $this->db->query('CREATE TABLE Showroom (
            idShowroom  INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_Showroom PRIMARY KEY,
            nom         VARCHAR(100) NOT NULL,
            adresse     VARCHAR(200) NOT NULL,
            codePostal  VARCHAR(5)   NOT NULL,
            ville       VARCHAR(100) NOT NULL,
            latitude    DECIMAL(9,6) NOT NULL,
            longitude   DECIMAL(9,6) NOT NULL,
            telephone   VARCHAR(20)  NULL,
            horaires    VARCHAR(200) NULL,
            image       VARCHAR(255) NULL
        )');

        $this->db->query("CREATE TABLE Evenement (
            idEvenement INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_Evenement PRIMARY KEY,
            titre       VARCHAR(150)  NOT NULL,
            description VARCHAR(1000) NULL,
            type        VARCHAR(20)   NOT NULL
                CONSTRAINT CK_Evenement_type CHECK (type IN ('demo', 'lancement', 'salon', 'atelier')),
            dateDebut   DATETIME NOT NULL,
            dateFin     DATETIME NOT NULL,
            idShowroom  INT NULL CONSTRAINT FK_Evenement_Showroom REFERENCES Showroom(idShowroom),
            idProduit   INT NULL CONSTRAINT FK_Evenement_Produit REFERENCES Produit(idProduit),
            CONSTRAINT CK_Evenement_dates CHECK (dateFin > dateDebut)
        )");

        $this->db->query("CREATE TABLE MessageContact (
            idMessage   INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_MessageContact PRIMARY KEY,
            nom         VARCHAR(100)  NOT NULL,
            email       VARCHAR(150)  NOT NULL,
            telephone   VARCHAR(20)   NULL,
            objet       VARCHAR(20)   NOT NULL
                CONSTRAINT CK_MessageContact_objet CHECK (objet IN ('demo', 'devis', 'sav', 'autre')),
            idProduit   INT NULL CONSTRAINT FK_MessageContact_Produit REFERENCES Produit(idProduit),
            message     VARCHAR(2000) NOT NULL,
            dateEnvoi   DATETIME NOT NULL CONSTRAINT DF_MessageContact_date DEFAULT GETDATE(),
            traite      BIT      NOT NULL CONSTRAINT DF_MessageContact_traite DEFAULT 0
        )");
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE MessageContact');
        $this->db->query('DROP TABLE Evenement');
        $this->db->query('DROP TABLE Showroom');
    }
}
