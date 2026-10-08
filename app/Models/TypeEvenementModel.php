<?php

namespace App\Models;

use CodeIgniter\Model;

/** Types d'événements (entité de base de l'AP3). */
class TypeEvenementModel extends Model
{
    protected $table            = 'TypeEvenement';
    protected $primaryKey       = 'code';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = ['code', 'libelle'];

    /** @return array<string, string> code => libellé, triés par code */
    public function liste(): array
    {
        return array_column($this->orderBy('code')->findAll(), 'libelle', 'code');
    }
}
