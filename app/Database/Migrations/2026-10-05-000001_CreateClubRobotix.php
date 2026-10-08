<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * AP2 — Club Robotix : catégories d'âge, réductions, tarifs, adhésions,
 * centres d'intérêt, places des événements et panier de réservations.
 */
class CreateClubRobotix extends Migration
{
    public function up(): void
    {
        $this->db->query('CREATE TABLE CategorieAge (
            idCategorieAge INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_CategorieAge PRIMARY KEY,
            libelle        VARCHAR(30) NOT NULL CONSTRAINT UQ_CategorieAge_libelle UNIQUE,
            ageMin         INT NOT NULL,
            ageMax         INT NOT NULL,
            CONSTRAINT CK_CategorieAge_bornes CHECK (ageMin >= 0 AND ageMin <= ageMax)
        )');

        $this->db->query('CREATE TABLE Reduction (
            idReduction    INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_Reduction PRIMARY KEY,
            idCategorieAge INT NOT NULL CONSTRAINT UQ_Reduction_categorie UNIQUE
                CONSTRAINT FK_Reduction_CategorieAge REFERENCES CategorieAge(idCategorieAge),
            txReduction    DECIMAL(5,2) NOT NULL CONSTRAINT CK_Reduction_taux CHECK (txReduction BETWEEN 0 AND 100)
        )');

        $this->db->query("CREATE TABLE Tarif (
            idTarif     INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_Tarif PRIMARY KEY,
            code        VARCHAR(30)  NOT NULL CONSTRAINT UQ_Tarif_code UNIQUE,
            libelle     VARCHAR(80)  NOT NULL,
            famille     VARCHAR(10)  NOT NULL CONSTRAINT CK_Tarif_famille CHECK (famille IN ('formule', 'option')),
            mode        VARCHAR(12)  NOT NULL CONSTRAINT CK_Tarif_mode CHECK (mode IN ('fixe', 'pourcentage')),
            valeur      DECIMAL(10,2) NOT NULL CONSTRAINT CK_Tarif_valeur CHECK (valeur >= 0),
            description VARCHAR(300) NULL,
            actif       BIT NOT NULL CONSTRAINT DF_Tarif_actif DEFAULT 1
        )");

        $this->db->query('ALTER TABLE Client ADD
            dateNaissance  DATE NULL,
            photo          VARCHAR(255) NULL,
            idCategorieAge INT NULL CONSTRAINT FK_Client_CategorieAge REFERENCES CategorieAge(idCategorieAge)');

        $this->db->query('ALTER TABLE Evenement ADD
            nbPlaces INT NOT NULL CONSTRAINT DF_Evenement_nbPlaces DEFAULT 30
                CONSTRAINT CK_Evenement_nbPlaces CHECK (nbPlaces >= 0)');

        $this->db->query('CREATE TABLE Adhesion (
            idAdhesion    INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_Adhesion PRIMARY KEY,
            idUtilisateur INT NOT NULL CONSTRAINT FK_Adhesion_Client REFERENCES Client(idUtilisateur),
            annee         INT NOT NULL CONSTRAINT CK_Adhesion_annee CHECK (annee BETWEEN 2000 AND 2100),
            dateAdhesion  DATETIME NOT NULL CONSTRAINT DF_Adhesion_date DEFAULT GETDATE(),
            idTarif       INT NOT NULL CONSTRAINT FK_Adhesion_Tarif REFERENCES Tarif(idTarif),
            montant       DECIMAL(10,2) NOT NULL CONSTRAINT CK_Adhesion_montant CHECK (montant >= 0),
            CONSTRAINT UQ_Adhesion_annee UNIQUE (idUtilisateur, annee)
        )');

        $this->db->query('CREATE TABLE ClientInteret (
            idUtilisateur INT NOT NULL CONSTRAINT FK_ClientInteret_Client REFERENCES Client(idUtilisateur),
            idCategorie   INT NOT NULL CONSTRAINT FK_ClientInteret_Categorie REFERENCES Categorie(idCategorie),
            CONSTRAINT PK_ClientInteret PRIMARY KEY (idUtilisateur, idCategorie)
        )');

        $this->db->query('CREATE TABLE Panier (
            idPanier      INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_Panier PRIMARY KEY,
            idEvenement   INT NOT NULL CONSTRAINT FK_Panier_Evenement REFERENCES Evenement(idEvenement),
            idUtilisateur INT NOT NULL CONSTRAINT FK_Panier_Client REFERENCES Client(idUtilisateur),
            nomEvenement  VARCHAR(150) NOT NULL,
            dateResa      DATETIME NOT NULL CONSTRAINT DF_Panier_date DEFAULT GETDATE(),
            nbPlace       INT NOT NULL CONSTRAINT CK_Panier_nbPlace CHECK (nbPlace > 0)
        )');
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE Panier');
        $this->db->query('DROP TABLE ClientInteret');
        $this->db->query('DROP TABLE Adhesion');
        $this->db->query('ALTER TABLE Evenement DROP CONSTRAINT CK_Evenement_nbPlaces, DF_Evenement_nbPlaces');
        $this->db->query('ALTER TABLE Evenement DROP COLUMN nbPlaces');
        $this->db->query('ALTER TABLE Client DROP CONSTRAINT FK_Client_CategorieAge');
        $this->db->query('ALTER TABLE Client DROP COLUMN dateNaissance, photo, idCategorieAge');
        $this->db->query('DROP TABLE Tarif');
        $this->db->query('DROP TABLE Reduction');
        $this->db->query('DROP TABLE CategorieAge');
    }
}
