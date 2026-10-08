<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;
use InvalidArgumentException;

/**
 * Membre (« adhérent joueur ») : spécialisation de Client, elle-même spécialisation d'Utilisateur.
 */
class MembreModel extends Model
{
    public const NIVEAUX = ['débutant', 'intermédiaire', 'confirmé'];

    protected $table            = 'Membre';
    protected $primaryKey       = 'idUtilisateur';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = ['idUtilisateur', 'niveau'];

    /** Reconstitue l'objet complet : Utilisateur + Client + Membre. */
    private function heritage(): BaseBuilder
    {
        return $this->db->table('Membre m')
            ->select('u.idUtilisateur, u.nom, u.prenom, u.email, u.pseudo, u.actif, c.telephone, c.dateNaissance, m.niveau, m.nbEvenements')
            ->join('Client c', 'c.idUtilisateur = m.idUtilisateur')
            ->join('Utilisateur u', 'u.idUtilisateur = c.idUtilisateur');
    }

    public function complets(): array
    {
        return $this->heritage()->orderBy('u.nom')->orderBy('u.prenom')->get()->getResultArray();
    }

    public function complet(int $id): ?array
    {
        return $this->heritage()->where('m.idUtilisateur', $id)->get()->getRowArray();
    }

    /** Un client devient membre du club ; un autre utilisateur est refusé (clé étrangère vers Client). */
    public function devenirMembre(int $idClient, string $niveau = 'débutant'): void
    {
        if ($this->db->table('Client')->where('idUtilisateur', $idClient)->countAllResults() === 0) {
            throw new InvalidArgumentException('Seul un client peut devenir membre du club.');
        }
        if (! in_array($niveau, self::NIVEAUX, true)) {
            throw new InvalidArgumentException('Niveau inconnu.');
        }

        $this->insert(['idUtilisateur' => $idClient, 'niveau' => $niveau]);
    }
}
