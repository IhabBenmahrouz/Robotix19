<?php

namespace App\Models;

use CodeIgniter\Model;
use DomainException;

/**
 * Table de relation Inscription (Membre ↔ Evenement) : présence et travail réalisé.
 * La clé primaire est composée (idMembre, idEvenement) : les écritures passent par le Query Builder.
 */
class InscriptionModel extends Model
{
    protected $table         = 'Inscription';
    protected $primaryKey    = 'idMembre';
    protected $returnType    = 'array';
    protected $allowedFields = ['idMembre', 'idEvenement', 'present', 'travailRealise'];

    public function estInscrit(int $idMembre, int $idEvenement): bool
    {
        return $this->db->table('Inscription')->where(['idMembre' => $idMembre, 'idEvenement' => $idEvenement])->countAllResults() > 0;
    }

    /** Inscrit un membre à un événement à venir qui a encore des places. */
    public function inscrire(int $idMembre, int $idEvenement): void
    {
        $evenement = $this->db->table('Evenement e')
            ->select('e.nbPlaces, CASE WHEN e.dateDebut > GETDATE() THEN 1 ELSE 0 END AS aVenir,
                      (SELECT COUNT(*) FROM Inscription i WHERE i.idEvenement = e.idEvenement)
                      + ISNULL((SELECT SUM(p.nbPlace) FROM Panier p WHERE p.idEvenement = e.idEvenement), 0) AS pris', false)
            ->where('e.idEvenement', $idEvenement)
            ->get()->getRowArray();

        if ($evenement === null) {
            throw new DomainException('Événement introuvable.');
        }
        if ((int) $evenement['aVenir'] === 0) {
            throw new DomainException('Cet événement est passé.');
        }
        if ($this->estInscrit($idMembre, $idEvenement)) {
            throw new DomainException('Vous êtes déjà inscrit à cet événement.');
        }
        if ((int) $evenement['pris'] >= (int) $evenement['nbPlaces']) {
            throw new DomainException('Cet événement est complet.');
        }

        $this->db->table('Inscription')->insert(['idMembre' => $idMembre, 'idEvenement' => $idEvenement]);
    }

    /** Inscrits d'un événement avec leur niveau, leur présence et leur travail. */
    public function inscritsDe(int $idEvenement): array
    {
        return $this->db->table('Inscription i')
            ->select('i.idMembre, u.nom, u.prenom, m.niveau, i.present, i.travailRealise')
            ->join('Membre m', 'm.idUtilisateur = i.idMembre')
            ->join('Utilisateur u', 'u.idUtilisateur = i.idMembre')
            ->where('i.idEvenement', $idEvenement)
            ->orderBy('u.nom')->orderBy('u.prenom')
            ->get()->getResultArray();
    }

    /** Enregistre la présence (null = non pointé) et le travail réalisé. */
    public function pointer(int $idMembre, int $idEvenement, ?bool $present, ?string $travail): void
    {
        $travail = $travail === null ? null : trim($travail);

        $this->db->table('Inscription')
            ->where(['idMembre' => $idMembre, 'idEvenement' => $idEvenement])
            ->update([
                'present'        => $present === null ? null : (int) $present,
                'travailRealise' => $travail === '' ? null : mb_substr((string) $travail, 0, 300),
            ]);
    }
}
