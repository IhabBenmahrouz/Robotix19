<?php

namespace App\Libraries;

use Config\Database;
use PDO;
use PDOStatement;
use Throwable;

/**
 * Accès aux données avec PDO (pilote pdo_sqlsrv) : une connexion unique,
 * des requêtes toujours préparées et des transactions.
 * Les paramètres de connexion sont ceux du groupe de base de données courant
 * (fichier .env ; groupe « tests » pendant PHPUnit).
 */
final class RobotixPdo
{
    private static ?self $instance = null;

    private PDO $pdo;
    private int $profondeur = 0;

    private function __construct(array $config)
    {
        $serveur = $config['hostname'] . (empty($config['port']) ? '' : ',' . $config['port']);

        $this->pdo = new PDO(
            "sqlsrv:Server={$serveur};Database={$config['database']};TrustServerCertificate=1",
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE                     => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE          => PDO::FETCH_ASSOC,
                PDO::SQLSRV_ATTR_ENCODING             => PDO::SQLSRV_ENCODING_UTF8,
                PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE => true,
            ],
        );
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            $config         = config(Database::class);
            self::$instance = new self($config->{$config->defaultGroup});
        }

        return self::$instance;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    /** Toutes les lignes du résultat. */
    public function lignes(string $sql, array $parametres = []): array
    {
        return $this->preparer($sql, $parametres)->fetchAll();
    }

    /** Première ligne, ou null si aucune. */
    public function ligne(string $sql, array $parametres = []): ?array
    {
        $ligne = $this->preparer($sql, $parametres)->fetch();

        return $ligne === false ? null : $ligne;
    }

    /** Première colonne de la première ligne, ou null. */
    public function valeur(string $sql, array $parametres = []): mixed
    {
        $valeur = $this->preparer($sql, $parametres)->fetchColumn();

        return $valeur === false ? null : $valeur;
    }

    /** INSERT / UPDATE / DELETE : nombre de lignes touchées. */
    public function executer(string $sql, array $parametres = []): int
    {
        return $this->preparer($sql, $parametres)->rowCount();
    }

    /**
     * Exécute $travail dans une transaction : validée s'il se termine, annulée s'il lève une exception.
     * Si une transaction est déjà ouverte, un point de sauvegarde SQL Server est utilisé.
     */
    public function transaction(callable $travail): mixed
    {
        $imbriquee = $this->pdo->inTransaction();
        $point     = 'rbx' . (++$this->profondeur);

        $imbriquee ? $this->pdo->exec("SAVE TRANSACTION {$point}") : $this->pdo->beginTransaction();

        try {
            $resultat = $travail($this);
            if (! $imbriquee) {
                $this->pdo->commit();
            }

            return $resultat;
        } catch (Throwable $erreur) {
            $imbriquee ? $this->pdo->exec("ROLLBACK TRANSACTION {$point}") : $this->pdo->rollBack();

            throw $erreur;
        } finally {
            $this->profondeur--;
        }
    }

    private function preparer(string $sql, array $parametres): PDOStatement
    {
        $requete = $this->pdo->prepare($sql);

        foreach ($parametres as $nom => $valeur) {
            $type = match (true) {
                $valeur === null => PDO::PARAM_NULL,
                is_int($valeur)  => PDO::PARAM_INT,
                is_bool($valeur) => PDO::PARAM_BOOL,
                default          => PDO::PARAM_STR,
            };
            $requete->bindValue(is_int($nom) ? $nom + 1 : ':' . ltrim($nom, ':'), $valeur, $type);
        }

        $requete->execute();

        return $requete;
    }
}
