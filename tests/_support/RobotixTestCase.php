<?php

namespace Tests\Support;

use App\Libraries\CalendrierDemo;
use App\Libraries\RobotixPdo;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Base des tests Robotix : chaque test tourne dans une transaction
 * annulée à la fin, la base Robotix58 retrouve donc son état d'origine.
 */
abstract class RobotixTestCase extends CIUnitTestCase
{
    use FeatureTestTrait;

    /** @var BaseConnection hérité de CIUnitTestCase */
    protected $db;

    protected RobotixPdo $pdo;

    protected function setUp(): void
    {
        parent::setUp();

        // Les formulaires sont testés sans jeton CSRF
        unset(config('Filters')->globals['before']['csrf']);

        // Deux connexions (CodeIgniter et PDO) : chacune lit les écritures non validées de l'autre
        // et échoue au bout de 5 s au lieu de rester bloquée.
        $reglages = 'SET LOCK_TIMEOUT 5000; SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;';

        $this->db = db_connect();
        $this->db->query($reglages);
        $this->db->transBegin();

        $this->pdo = RobotixPdo::instance();
        $this->pdo->pdo()->exec($reglages);
        $this->pdo->pdo()->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->pdo->pdo()->inTransaction()) {
            $this->pdo->pdo()->rollBack();
        }
        $this->db->transRollback();
        parent::tearDown();
    }

    /** Date du jeu d'essai telle que le seeder l'a écrite (décalage du calendrier de démo). */
    protected function demo(string $date): string
    {
        return CalendrierDemo::courant()->date($date);
    }

    /** Année d'adhésion du jeu d'essai (2025 ou 2026 avant décalage). */
    protected function anneeDemo(int $annee): int
    {
        return CalendrierDemo::courant()->annee($annee);
    }

    /** Date du jeu d'essai au format français « JJ/MM/AAAA ». */
    protected function demoFr(string $date): string
    {
        return date('d/m/Y', strtotime($this->demo($date)));
    }

    protected function idProduit(string $reference): int
    {
        return (int) $this->db->table('Produit')->select('idProduit')
            ->where('reference', $reference)->get()->getRow('idProduit');
    }

    protected function idUtilisateur(string $email): int
    {
        return (int) $this->db->table('Utilisateur')->select('idUtilisateur')
            ->where('email', $email)->get()->getRow('idUtilisateur');
    }

    /** Données de session d'un compte de démo, pour withSession(). */
    protected function sessionDe(string $email): array
    {
        $roles = ['admin@robotix.test' => 'admin', 'redacteur@robotix.test' => 'redacteur'];

        $id = $this->idUtilisateur($email);

        return [
            'idUtilisateur' => $id,
            'nom'           => 'Test',
            'prenom'        => 'Test',
            'role'          => $roles[$email] ?? 'client',
            'profilClub'    => model(\App\Models\UtilisateurModel::class)->profilsClub($id),
        ];
    }
}
