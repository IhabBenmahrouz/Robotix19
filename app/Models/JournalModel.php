<?php

namespace App\Models;

use CodeIgniter\Model;

/** Journal des actions d'administration (alimenté par App\Libraries\Journaliseur). */
class JournalModel extends Model
{
    protected $table      = 'Journal';
    protected $primaryKey = 'idJournal';
    protected $returnType = 'array';

    /** Applique les filtres (type d'action, table) et joint l'auteur ; à enchaîner avec paginate(). */
    public function filtre(?string $type, ?string $table): self
    {
        $this->select('Journal.*, u.prenom, u.nom')
            ->join('Utilisateur u', 'u.idUtilisateur = Journal.idUtilisateur', 'left')
            ->orderBy('Journal.dateAction', 'DESC')->orderBy('Journal.idJournal', 'DESC');

        if ($type !== null && $type !== '') {
            $this->where('Journal.typeAction', $type);
        }
        if ($table !== null && $table !== '') {
            $this->where('Journal.tableCible', $table);
        }

        return $this;
    }

    /** @return list<string> valeurs distinctes d'une colonne, pour les listes déroulantes */
    public function valeurs(string $colonne): array
    {
        return array_column($this->builder()->distinct()->select($colonne)->orderBy($colonne)->get()->getResultArray(), $colonne);
    }
}
