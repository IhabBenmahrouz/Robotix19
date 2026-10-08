<?php

namespace App\Models;

use CodeIgniter\Model;

class MarqueModel extends Model
{
    protected $table         = 'Marque';
    protected $primaryKey    = 'idMarque';
    protected $returnType    = 'array';
    protected $allowedFields = ['nom', 'pays', 'siteWeb', 'logo', 'description', 'ticker', 'placeMarche'];
}
