<?php

namespace App\Models;

use CodeIgniter\Model;

class ImageModel extends Model
{
    protected $table         = 'Image';
    protected $primaryKey    = 'idImage';
    protected $returnType    = 'array';
    protected $allowedFields = ['fichier', 'legende', 'numOrdre', 'idProduit'];

    public function duProduit(int $idProduit): array
    {
        return $this->where('idProduit', $idProduit)->orderBy('numOrdre')->findAll();
    }
}
