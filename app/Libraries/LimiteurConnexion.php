<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Limite les tentatives de connexion : au-delà de MAX_ECHECS échecs pour un identifiant
 * (ou MAX_ECHECS_IP pour une adresse IP) en FENETRE_MINUTES minutes, la connexion est
 * refusée jusqu'à FENETRE_MINUTES minutes après le dernier échec.
 * Les durées sont calculées par SQL Server (GETDATE()), sans conversion de dates en PHP.
 */
final class LimiteurConnexion
{
    public const MAX_ECHECS      = 5;
    public const MAX_ECHECS_IP   = 20;
    public const FENETRE_MINUTES = 15;

    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /** Identifiant comparé sans casse ni espaces (« Client@Robotix.TEST » = « client@robotix.test »). */
    public static function normaliser(string $identifiant): string
    {
        return mb_substr(mb_strtolower(trim($identifiant)), 0, 150);
    }

    /** Minutes restantes avant de pouvoir réessayer (0 = connexion autorisée). */
    public function minutesDeBlocage(string $identifiant, string $ip): int
    {
        $fenetre = self::FENETRE_MINUTES;
        $echecsIp = (int) $this->db->query("SELECT COUNT(*) AS n FROM TentativeConnexion
            WHERE ip = ? AND reussie = 0 AND dateTentative > DATEADD(MINUTE, -$fenetre, GETDATE())", [$ip])->getRow('n');

        if ($this->echecs($identifiant) < self::MAX_ECHECS && $echecsIp < self::MAX_ECHECS_IP) {
            return 0;
        }

        // Blocage jusqu'à FENETRE_MINUTES minutes après le dernier échec
        $secondes = (int) $this->db->query("SELECT DATEDIFF(SECOND, GETDATE(), DATEADD(MINUTE, $fenetre, MAX(dateTentative))) AS s
            FROM TentativeConnexion WHERE (identifiant = ? OR ip = ?) AND reussie = 0", [self::normaliser($identifiant), $ip])->getRow('s');

        return max(1, (int) ceil($secondes / 60));
    }

    /** Essais restants pour cet identifiant avant blocage. */
    public function essaisRestants(string $identifiant): int
    {
        return max(0, self::MAX_ECHECS - $this->echecs($identifiant));
    }

    /** Échecs récents de l'identifiant, depuis sa dernière connexion réussie. */
    private function echecs(string $identifiant): int
    {
        $fenetre = self::FENETRE_MINUTES;

        return (int) $this->db->query("SELECT COUNT(*) AS n FROM TentativeConnexion t
            WHERE t.identifiant = ? AND t.reussie = 0 AND t.dateTentative > DATEADD(MINUTE, -$fenetre, GETDATE())
              AND t.dateTentative > ISNULL((SELECT MAX(s.dateTentative) FROM TentativeConnexion s
                                            WHERE s.identifiant = t.identifiant AND s.reussie = 1), '19000101')",
            [self::normaliser($identifiant)])->getRow('n');
    }

    public function noter(string $identifiant, string $ip, bool $reussie): void
    {
        $this->db->table('TentativeConnexion')->insert([
            'identifiant' => self::normaliser($identifiant),
            'ip'          => mb_substr($ip, 0, 45),
            'reussie'     => $reussie ? 1 : 0,
        ]);
    }
}
