<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * AP3 — Club Robotix : héritage des adhérents (Membre / Animateur), animateur remplaçant,
 * types d'événements, inscriptions avec présence et travail, réunions internes,
 * mails générés et historique des adhésions.
 */
class CreateClubAp3 extends Migration
{
    public function up(): void
    {
        // Connexion par pseudo : facultatif mais unique
        $this->db->query('ALTER TABLE Utilisateur ADD pseudo VARCHAR(30) NULL');
        $this->db->query('CREATE UNIQUE INDEX UX_Utilisateur_pseudo ON Utilisateur(pseudo) WHERE pseudo IS NOT NULL');

        // Héritage : un client (adhérent) peut être membre (joueur) et/ou animateur (entraîneur)
        $this->db->query("CREATE TABLE Membre (
            idUtilisateur INT NOT NULL CONSTRAINT PK_Membre PRIMARY KEY
                CONSTRAINT FK_Membre_Client REFERENCES Client(idUtilisateur),
            niveau        VARCHAR(20) NOT NULL CONSTRAINT DF_Membre_niveau DEFAULT 'débutant'
                CONSTRAINT CK_Membre_niveau CHECK (niveau IN ('débutant', 'intermédiaire', 'confirmé')),
            nbEvenements  INT NOT NULL CONSTRAINT DF_Membre_nb DEFAULT 0
        )");

        $this->db->query('CREATE TABLE Animateur (
            idUtilisateur INT NOT NULL CONSTRAINT PK_Animateur PRIMARY KEY
                CONSTRAINT FK_Animateur_Client REFERENCES Client(idUtilisateur),
            specialite    VARCHAR(80) NOT NULL,
            idRemplacant  INT NULL CONSTRAINT FK_Animateur_Remplacant REFERENCES Animateur(idUtilisateur),
            CONSTRAINT CK_Animateur_remplacant CHECK (idRemplacant <> idUtilisateur)
        )');

        // Type d'événement : entité de base, référencée par Evenement.type
        $this->db->query('CREATE TABLE TypeEvenement (
            code    VARCHAR(20) NOT NULL CONSTRAINT PK_TypeEvenement PRIMARY KEY,
            libelle VARCHAR(40) NOT NULL
        )');
        $this->db->query("INSERT INTO TypeEvenement (code, libelle) VALUES
            ('demo', 'Démonstration'), ('lancement', 'Lancement'), ('salon', 'Salon'), ('atelier', 'Atelier')");
        $this->db->query('ALTER TABLE Evenement DROP CONSTRAINT CK_Evenement_type');
        $this->db->query('ALTER TABLE Evenement ADD CONSTRAINT FK_Evenement_Type FOREIGN KEY (type) REFERENCES TypeEvenement(code)');
        $this->db->query('ALTER TABLE Evenement ADD idAnimateur INT NULL CONSTRAINT FK_Evenement_Animateur REFERENCES Animateur(idUtilisateur)');

        // Inscription d'un membre à un événement : présence et travail réalisé
        $this->db->query('CREATE TABLE Inscription (
            idMembre        INT NOT NULL CONSTRAINT FK_Inscription_Membre REFERENCES Membre(idUtilisateur),
            idEvenement     INT NOT NULL CONSTRAINT FK_Inscription_Evenement REFERENCES Evenement(idEvenement),
            dateInscription DATETIME NOT NULL CONSTRAINT DF_Inscription_date DEFAULT GETDATE(),
            present         BIT NULL,
            travailRealise  VARCHAR(300) NULL,
            CONSTRAINT PK_Inscription PRIMARY KEY (idMembre, idEvenement)
        )');

        // Réunions internes, convocations des animateurs, ordre du jour ordonné
        $this->db->query('CREATE TABLE Reunion (
            idReunion   INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_Reunion PRIMARY KEY,
            dateReunion DATETIME NOT NULL,
            objet       VARCHAR(150) NOT NULL,
            idShowroom  INT NULL CONSTRAINT FK_Reunion_Showroom REFERENCES Showroom(idShowroom)
        )');
        $this->db->query('CREATE TABLE Convocation (
            idReunion   INT NOT NULL CONSTRAINT FK_Convocation_Reunion REFERENCES Reunion(idReunion),
            idAnimateur INT NOT NULL CONSTRAINT FK_Convocation_Animateur REFERENCES Animateur(idUtilisateur),
            dateEnvoi   DATETIME NOT NULL CONSTRAINT DF_Convocation_date DEFAULT GETDATE(),
            CONSTRAINT PK_Convocation PRIMARY KEY (idReunion, idAnimateur)
        )');
        $this->db->query('CREATE TABLE PointOrdreJour (
            idReunion INT NOT NULL CONSTRAINT FK_Point_Reunion REFERENCES Reunion(idReunion),
            numOrdre  INT NOT NULL CONSTRAINT CK_Point_ordre CHECK (numOrdre >= 1),
            libelle   VARCHAR(200) NOT NULL,
            CONSTRAINT PK_PointOrdreJour PRIMARY KEY (idReunion, numOrdre)
        )');

        // Mails générés par déclencheur et historique des adhésions non renouvelées
        $this->db->query('CREATE TABLE MailAEnvoyer (
            idMail       INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_MailAEnvoyer PRIMARY KEY,
            destinataire VARCHAR(150) NOT NULL,
            objet        VARCHAR(150) NOT NULL,
            corps        VARCHAR(2000) NOT NULL,
            dateCreation DATETIME NOT NULL CONSTRAINT DF_Mail_date DEFAULT GETDATE(),
            envoye       BIT NOT NULL CONSTRAINT DF_Mail_envoye DEFAULT 0
        )');
        $this->db->query('CREATE TABLE HistoriqueAdhesion (
            idHistorique      INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_HistoriqueAdhesion PRIMARY KEY,
            idUtilisateur     INT NOT NULL,
            nom               VARCHAR(50) NULL,
            prenom            VARCHAR(50) NULL,
            derniereAnnee     INT NULL,
            motif             VARCHAR(100) NOT NULL,
            dateHistorisation DATETIME NOT NULL CONSTRAINT DF_Historique_date DEFAULT GETDATE()
        )');
    }

    public function down(): void
    {
        foreach (['HistoriqueAdhesion', 'MailAEnvoyer', 'PointOrdreJour', 'Convocation', 'Reunion', 'Inscription'] as $table) {
            $this->db->query("DROP TABLE {$table}");
        }
        $this->db->query('ALTER TABLE Evenement DROP CONSTRAINT FK_Evenement_Animateur');
        $this->db->query('ALTER TABLE Evenement DROP COLUMN idAnimateur');
        $this->db->query('ALTER TABLE Evenement DROP CONSTRAINT FK_Evenement_Type');
        $this->db->query("ALTER TABLE Evenement ADD CONSTRAINT CK_Evenement_type CHECK (type IN ('demo', 'lancement', 'salon', 'atelier'))");
        $this->db->query('DROP TABLE TypeEvenement');
        $this->db->query('DROP TABLE Animateur');
        $this->db->query('DROP TABLE Membre');
        $this->db->query('DROP INDEX UX_Utilisateur_pseudo ON Utilisateur');
        $this->db->query('ALTER TABLE Utilisateur DROP COLUMN pseudo');
    }
}
