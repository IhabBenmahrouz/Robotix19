<?php

namespace App\Models;

use CodeIgniter\Model;

class ShowroomModel extends Model
{
    protected $table         = 'Showroom';
    protected $primaryKey    = 'idShowroom';
    protected $returnType    = 'array';
    protected $allowedFields = ['nom', 'adresse', 'codePostal', 'ville', 'latitude', 'longitude', 'telephone', 'horaires', 'image'];
}
