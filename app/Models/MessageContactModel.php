<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageContactModel extends Model
{
    public const OBJETS = [
        'demo'  => 'Demande de démonstration',
        'devis' => 'Demande de devis',
        'sav'   => 'Service après-vente',
        'autre' => 'Autre question',
    ];

    protected $table         = 'MessageContact';
    protected $primaryKey    = 'idMessage';
    protected $returnType    = 'array';
    protected $allowedFields = ['nom', 'email', 'telephone', 'objet', 'idProduit', 'message', 'traite'];
}
