<?php

namespace App\Models;

use CodeIgniter\Model;

class AdresseModel extends Model
{
    protected $table         = 'Adresse';
    protected $primaryKey    = 'idAdresse';
    protected $returnType    = 'array';
    protected $allowedFields = ['libelle', 'ligne1', 'ligne2', 'codePostal', 'ville', 'pays', 'idUtilisateur'];
}
