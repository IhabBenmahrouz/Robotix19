<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table            = 'Client';
    protected $primaryKey       = 'idUtilisateur';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = ['idUtilisateur', 'telephone'];
}
