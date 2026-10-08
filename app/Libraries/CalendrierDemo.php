<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use DateInterval;
use DateTimeImmutable;

/**
 * Calendrier du jeu de démonstration : les dates du seeder sont écrites par rapport
 * au lundi 5 octobre 2026 et décalées d'un nombre entier de semaines pour rester
 * toujours « actuelles » (jours de la semaine, horaires et âges conservés).
 */
final class CalendrierDemo
{
    public const REFERENCE = '2026-10-05';

    private const CLE = 'calendrier.semaines';

    public function __construct(private int $semaines)
    {
    }

    public function semaines(): int
    {
        return $this->semaines;
    }

    /** Décale le jour d'une date « AAAA-MM-JJ… » ; l'heure qui suit éventuellement est conservée telle quelle. */
    public function date(string $iso): string
    {
        $jour = new DateTimeImmutable(substr($iso, 0, 10));

        return $jour->add(new DateInterval('P' . ($this->semaines * 7) . 'D'))->format('Y-m-d') . substr($iso, 10);
    }

    /** Année d'adhésion décalée comme l'année de la date de référence. */
    public function annee(int $annee): int
    {
        return $annee + ((int) substr($this->date(self::REFERENCE), 0, 4) - 2026);
    }

    /** Nombre de semaines entières écoulées depuis la référence (jamais négatif). */
    public static function semainesDepuisReference(string $aujourdhui): int
    {
        $jours = (int) (new DateTimeImmutable(self::REFERENCE))->diff(new DateTimeImmutable($aujourdhui))->format('%r%a');

        return max(0, intdiv($jours, 7));
    }

    /** Enregistre en base le décalage utilisé lors du dernier seeding (table ParametreDemo). */
    public static function memoriser(int $semaines, ?BaseConnection $db = null): void
    {
        $db ??= db_connect();
        $db->table('ParametreDemo')->where('cle', self::CLE)->delete();
        $db->table('ParametreDemo')->set(['cle' => self::CLE, 'valeur' => (string) $semaines])
            ->set('dateMaj', 'GETDATE()', false)->insert();
    }

    /** Date du dernier seeding (« AAAA-MM-JJ HH:MM:SS… »), null si inconnue. */
    public static function derniereReinitialisation(?BaseConnection $db = null): ?string
    {
        $ligne = ($db ?? db_connect())->table('ParametreDemo')->where('cle', self::CLE)->get()->getRowArray();

        return $ligne['dateMaj'] ?? null;
    }

    /** Décalage du dernier seeding (0 si la base n'a jamais été remplie par le seeder). */
    public static function courant(?BaseConnection $db = null): self
    {
        $ligne = ($db ?? db_connect())->table('ParametreDemo')->where('cle', self::CLE)->get()->getRowArray();

        return new self((int) ($ligne['valeur'] ?? 0));
    }
}
