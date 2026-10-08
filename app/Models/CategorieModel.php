<?php

namespace App\Models;

use CodeIgniter\Model;

class CategorieModel extends Model
{
    protected $table         = 'Categorie';
    protected $primaryKey    = 'idCategorie';
    protected $returnType    = 'array';
    protected $allowedFields = ['libelle', 'description'];
}
