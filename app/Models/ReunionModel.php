<?php

namespace App\Models;

use CodeIgniter\Model;

/** Réunions internes, convocations des animateurs et ordre du jour. */
class ReunionModel extends Model
{
    protected $table         = 'Reunion';
    protected $primaryKey    = 'idReunion';
    protected $returnType    = 'array';
    protected $allowedFields = ['dateReunion', 'objet', 'idShowroom'];

    public function avecDetails(): array
    {
        return $this->db->table('Reunion r')
            ->select('r.idReunion, r.dateReunion, r.objet, s.nom AS lieu,
                      (SELECT COUNT(*) FROM Convocation c WHERE c.idReunion = r.idReunion) AS nbConvoques,
                      (SELECT COUNT(*) FROM PointOrdreJour p WHERE p.idReunion = r.idReunion) AS nbPoints', false)
            ->join('Showroom s', 's.idShowroom = r.idShowroom', 'left')
            ->orderBy('r.dateReunion')
            ->get()->getResultArray();
    }

    /** Animateurs convoqués (table de relation Convocation). */
    public function convoques(int $idReunion): array
    {
        return $this->db->table('Convocation c')
            ->select('u.idUtilisateur, u.nom, u.prenom, c.dateEnvoi')
            ->join('Utilisateur u', 'u.idUtilisateur = c.idAnimateur')
            ->where('c.idReunion', $idReunion)
            ->orderBy('u.idUtilisateur')
            ->get()->getResultArray();
    }

    /** Points de l'ordre du jour, dans l'ordre. */
    public function ordreDuJour(int $idReunion): array
    {
        return $this->db->table('PointOrdreJour')
            ->where('idReunion', $idReunion)->orderBy('numOrdre')
            ->get()->getResultArray();
    }
}
