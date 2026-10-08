<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Jetons de réinitialisation du mot de passe : seule l'empreinte SHA-256 du jeton est stockée.
 */
class CreateJetonMotDePasse extends Migration
{
    public function up(): void
    {
        $this->db->query('CREATE TABLE JetonMotDePasse (
            idJeton        INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_JetonMotDePasse PRIMARY KEY,
            idUtilisateur  INT       NOT NULL CONSTRAINT FK_JetonMotDePasse_Utilisateur REFERENCES Utilisateur(idUtilisateur) ON DELETE CASCADE,
            empreinte      CHAR(64)  NOT NULL CONSTRAINT UQ_JetonMotDePasse_empreinte UNIQUE,
            dateCreation   DATETIME  NOT NULL CONSTRAINT DF_JetonMotDePasse_creation DEFAULT GETDATE(),
            dateExpiration DATETIME  NOT NULL,
            utilise        BIT       NOT NULL CONSTRAINT DF_JetonMotDePasse_utilise DEFAULT 0
        )');
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE JetonMotDePasse');
    }
}
