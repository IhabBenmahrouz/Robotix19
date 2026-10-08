<?php

namespace App\Models\Pdo;

use App\Libraries\RobotixPdo;

/**
 * Statistiques des adhésions au Club Robotix (requêtes PDO préparées).
 */
class StatistiquesPdo
{
    private RobotixPdo $pdo;

    public function __construct(?RobotixPdo $pdo = null)
    {
        $this->pdo = $pdo ?? RobotixPdo::instance();
    }

    /** Fonction sans paramètre : montant total des adhésions de l'année en cours. */
    public function montantTotalAdhesions(): float
    {
        return (float) $this->pdo->valeur('SELECT ISNULL(SUM(montant), 0) FROM Adhesion WHERE annee = YEAR(GETDATE())');
    }

    /** Fonction avec paramètre : montant total des adhésions d'une année. */
    public function montantAdhesions(int $annee): float
    {
        return (float) $this->pdo->valeur('SELECT ISNULL(SUM(montant), 0) FROM Adhesion WHERE annee = :annee', ['annee' => $annee]);
    }

    /** Nombre et taux (%) d'adhésions par catégorie d'âge pour une année. */
    public function adhesionsParCategorie(int $annee): array
    {
        $lignes = $this->pdo->lignes(
            'SELECT ca.idCategorieAge, ca.libelle, COUNT(a.idAdhesion) AS nombre
             FROM CategorieAge ca
             LEFT JOIN Client c ON c.idCategorieAge = ca.idCategorieAge
             LEFT JOIN Adhesion a ON a.idUtilisateur = c.idUtilisateur AND a.annee = :annee
             GROUP BY ca.idCategorieAge, ca.libelle, ca.ageMin
             ORDER BY ca.ageMin',
            ['annee' => $annee],
        );
        $total = array_sum(array_column($lignes, 'nombre'));

        return array_map(static fn (array $l): array => [
            'idCategorieAge' => (int) $l['idCategorieAge'],
            'libelle'        => $l['libelle'],
            'nombre'         => (int) $l['nombre'],
            'taux'           => $total > 0 ? round($l['nombre'] * 100 / $total, 1) : 0.0,
        ], $lignes);
    }

    /** Fonction avec deux paramètres : adhésions d'une catégorie pour une année. */
    public function nombreAdhesions(int $idCategorieAge, int $annee): int
    {
        return (int) $this->pdo->valeur(
            'SELECT COUNT(*) FROM Adhesion a JOIN Client c ON c.idUtilisateur = a.idUtilisateur
             WHERE c.idCategorieAge = :categorie AND a.annee = :annee',
            ['categorie' => $idCategorieAge, 'annee' => $annee],
        );
    }

    /** @return list<int> années ayant des adhésions, plus l'année en cours, de la plus récente à la plus ancienne */
    public function anneesDisponibles(): array
    {
        $annees   = array_map('intval', array_column($this->pdo->lignes('SELECT DISTINCT annee FROM Adhesion'), 'annee'));
        $annees[] = (int) date('Y');
        $annees   = array_values(array_unique($annees));
        rsort($annees);

        return $annees;
    }
}
