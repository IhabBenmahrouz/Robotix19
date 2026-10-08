<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * AP3 — Programmation de la base SQL Server : 5 procédures stockées, 3 déclencheurs, 2 vues.
 * Chaque objet est créé dans son propre lot (CREATE OR ALTER).
 */
class CreateProgrammationAp3 extends Migration
{
    public function up(): void
    {
        foreach ($this->definitions() as $sql) {
            $this->db->query($sql);
        }
    }

    public function down(): void
    {
        foreach (['trg_Utilisateur_Historisation', 'trg_Inscription_NbEvenements', 'trg_Inscription_Mail'] as $declencheur) {
            $this->db->query("DROP TRIGGER IF EXISTS {$declencheur}");
        }
        foreach (['ps_HeuresEntrainement', 'ps_NbEvenementsEntreDates', 'ps_EvenementsSuivis', 'ps_OrdreDuJour', 'ps_AdherentsRenouveles'] as $procedure) {
            $this->db->query("DROP PROCEDURE IF EXISTS {$procedure}");
        }
        $this->db->query('DROP VIEW IF EXISTS v_AdherentsRoles');
        $this->db->query('DROP VIEW IF EXISTS v_EvenementsPresents');
    }

    /** @return list<string> */
    private function definitions(): array
    {
        return [
            // ---------- Procédures stockées ----------
            "CREATE OR ALTER PROCEDURE ps_AdherentsRenouveles @annee INT
            AS
            BEGIN
                -- Adhérents ayant une adhésion pour @annee ET pour l'année précédente
                SET NOCOUNT ON;
                SELECT u.idUtilisateur, u.nom, u.prenom, u.email,
                       tp.libelle AS formulePrecedente, tn.libelle AS formuleAnnee
                FROM Adhesion n
                JOIN Adhesion p    ON p.idUtilisateur = n.idUtilisateur AND p.annee = @annee - 1
                JOIN Utilisateur u ON u.idUtilisateur = n.idUtilisateur
                JOIN Tarif tn      ON tn.idTarif = n.idTarif
                JOIN Tarif tp      ON tp.idTarif = p.idTarif
                WHERE n.annee = @annee
                ORDER BY u.nom, u.prenom;
            END",

            "CREATE OR ALTER PROCEDURE ps_OrdreDuJour @date DATE
            AS
            BEGIN
                -- Points à traiter des réunions d'une date, dans l'ordre
                SET NOCOUNT ON;
                SELECT r.idReunion, r.objet, CONVERT(VARCHAR(5), r.dateReunion, 108) AS heure,
                       p.numOrdre, p.libelle
                FROM Reunion r
                JOIN PointOrdreJour p ON p.idReunion = r.idReunion
                WHERE CAST(r.dateReunion AS DATE) = @date
                ORDER BY r.dateReunion, p.numOrdre;
            END",

            "CREATE OR ALTER PROCEDURE ps_EvenementsSuivis @idAdherent INT
            AS
            BEGIN
                -- Événements suivis par un adhérent : présence et travail réalisé
                SET NOCOUNT ON;
                SELECT u.nom, u.prenom, e.idEvenement, e.titre, e.dateDebut,
                       CASE i.present WHEN 1 THEN N'Présent' WHEN 0 THEN N'Absent' ELSE N'Non pointé' END AS presence,
                       i.travailRealise
                FROM Inscription i
                JOIN Evenement e   ON e.idEvenement = i.idEvenement
                JOIN Utilisateur u ON u.idUtilisateur = i.idMembre
                WHERE i.idMembre = @idAdherent
                ORDER BY e.dateDebut;
            END",

            "CREATE OR ALTER PROCEDURE ps_NbEvenementsEntreDates
                @idMembre INT, @debut DATE, @fin DATE, @nb INT OUTPUT
            AS
            BEGIN
                -- Nombre d'événements suivis (présent) par un joueur entre deux dates incluses
                SET NOCOUNT ON;
                SELECT @nb = COUNT(*)
                FROM Inscription i
                JOIN Evenement e ON e.idEvenement = i.idEvenement
                WHERE i.idMembre = @idMembre AND i.present = 1
                  AND CAST(e.dateDebut AS DATE) BETWEEN @debut AND @fin;
            END",

            "CREATE OR ALTER PROCEDURE ps_HeuresEntrainement
            AS
            BEGIN
                -- Heures d'atelier suivies (présent) par chaque joueur
                SET NOCOUNT ON;
                SELECT m.idUtilisateur, u.nom, u.prenom,
                       CAST(ISNULL(SUM(CASE WHEN i.present = 1 AND e.type = 'atelier'
                                            THEN DATEDIFF(MINUTE, e.dateDebut, e.dateFin) END), 0) / 60.0
                            AS DECIMAL(6,2)) AS heures
                FROM Membre m
                JOIN Utilisateur u     ON u.idUtilisateur = m.idUtilisateur
                LEFT JOIN Inscription i ON i.idMembre = m.idUtilisateur
                LEFT JOIN Evenement e   ON e.idEvenement = i.idEvenement
                GROUP BY m.idUtilisateur, u.nom, u.prenom
                ORDER BY heures DESC, u.nom;
            END",

            // ---------- Déclencheurs ----------
            "CREATE OR ALTER TRIGGER trg_Inscription_Mail ON Inscription
            AFTER INSERT
            AS
            BEGIN
                -- Un mail de confirmation par inscription (gère les insertions multiples)
                SET NOCOUNT ON;
                INSERT INTO MailAEnvoyer (destinataire, objet, corps)
                SELECT u.email,
                       LEFT(N'Inscription confirmée : ' + e.titre, 150),
                       LEFT(N'Bonjour ' + u.prenom + N', votre inscription à « ' + e.titre + N' » le '
                            + CONVERT(VARCHAR(10), e.dateDebut, 103) + N' à ' + CONVERT(VARCHAR(5), e.dateDebut, 108)
                            + ISNULL(N' (' + s.nom + N')', N'') + N' est confirmée. À bientôt au Club Robotix !', 2000)
                FROM inserted i
                JOIN Utilisateur u  ON u.idUtilisateur = i.idMembre
                JOIN Evenement e    ON e.idEvenement = i.idEvenement
                LEFT JOIN Showroom s ON s.idShowroom = e.idShowroom;
            END",

            "CREATE OR ALTER TRIGGER trg_Inscription_NbEvenements ON Inscription
            AFTER INSERT, UPDATE, DELETE
            AS
            BEGIN
                -- Recalcule le nombre d'événements des membres concernés
                SET NOCOUNT ON;
                UPDATE m
                SET nbEvenements = (SELECT COUNT(*) FROM Inscription x WHERE x.idMembre = m.idUtilisateur)
                FROM Membre m
                WHERE m.idUtilisateur IN (SELECT idMembre FROM inserted UNION SELECT idMembre FROM deleted);
            END",

            "CREATE OR ALTER TRIGGER trg_Utilisateur_Historisation ON Utilisateur
            AFTER UPDATE
            AS
            BEGIN
                -- Adhérent désactivé sans adhésion pour l'année en cours : historisation
                SET NOCOUNT ON;
                IF NOT UPDATE(actif) RETURN;
                INSERT INTO HistoriqueAdhesion (idUtilisateur, nom, prenom, derniereAnnee, motif)
                SELECT i.idUtilisateur, i.nom, i.prenom,
                       (SELECT MAX(a.annee) FROM Adhesion a WHERE a.idUtilisateur = i.idUtilisateur),
                       N'Adhésion non renouvelée'
                FROM inserted i
                JOIN deleted d ON d.idUtilisateur = i.idUtilisateur
                JOIN Client c  ON c.idUtilisateur = i.idUtilisateur
                WHERE d.actif = 1 AND i.actif = 0
                  AND NOT EXISTS (SELECT 1 FROM Adhesion a
                                  WHERE a.idUtilisateur = i.idUtilisateur AND a.annee = YEAR(GETDATE()));
            END",

            // ---------- Vues ----------
            'CREATE OR ALTER VIEW v_EvenementsPresents
            AS
            SELECT e.idEvenement, e.titre, e.dateDebut, e.type, u.idUtilisateur, u.nom, u.prenom, i.travailRealise
            FROM Inscription i
            JOIN Evenement e   ON e.idEvenement = i.idEvenement
            JOIN Utilisateur u ON u.idUtilisateur = i.idMembre
            WHERE i.present = 1',

            "CREATE OR ALTER VIEW v_AdherentsRoles
            AS
            SELECT u.idUtilisateur, u.nom, u.prenom, u.email,
                   CAST(N'Entraîneur' AS NVARCHAR(20)) AS role, CAST(a.specialite AS NVARCHAR(80)) AS detail
            FROM Animateur a JOIN Utilisateur u ON u.idUtilisateur = a.idUtilisateur
            UNION ALL
            SELECT u.idUtilisateur, u.nom, u.prenom, u.email,
                   CAST(N'Joueur' AS NVARCHAR(20)), CAST(m.niveau AS NVARCHAR(80))
            FROM Membre m JOIN Utilisateur u ON u.idUtilisateur = m.idUtilisateur",
        ];
    }
}
