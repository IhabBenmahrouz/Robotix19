<?php

namespace App\Libraries;

/**
 * Appels des procédures stockées de l'AP3 (requêtes PDO préparées).
 */
class ProceduresAp3
{
    private RobotixPdo $pdo;

    public function __construct(?RobotixPdo $pdo = null)
    {
        $this->pdo = $pdo ?? RobotixPdo::instance();
    }

    public function adherentsRenouveles(int $annee): array
    {
        return $this->pdo->lignes('EXEC ps_AdherentsRenouveles @annee = :annee', ['annee' => $annee]);
    }

    /** @param string $date AAAA-MM-JJ */
    public function ordreDuJour(string $date): array
    {
        return $this->pdo->lignes('EXEC ps_OrdreDuJour @date = :date', ['date' => $date]);
    }

    public function evenementsSuivis(int $idAdherent): array
    {
        return $this->pdo->lignes('EXEC ps_EvenementsSuivis @idAdherent = :id', ['id' => $idAdherent]);
    }

    /** Récupère le paramètre OUTPUT @nb de la procédure. */
    public function nbEvenementsEntreDates(int $idMembre, string $debut, string $fin): int
    {
        return (int) $this->pdo->valeur(
            'SET NOCOUNT ON;
             DECLARE @nb INT;
             EXEC ps_NbEvenementsEntreDates @idMembre = :membre, @debut = :debut, @fin = :fin, @nb = @nb OUTPUT;
             SELECT @nb AS nb;',
            ['membre' => $idMembre, 'debut' => $debut, 'fin' => $fin],
        );
    }

    public function heuresEntrainement(): array
    {
        return $this->pdo->lignes('EXEC ps_HeuresEntrainement');
    }
}
