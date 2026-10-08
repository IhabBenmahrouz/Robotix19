<?php

namespace App\Models;

use CodeIgniter\Model;

/** Mails à envoyer : déclencheur trg_Inscription_Mail et liens « mot de passe oublié ». */
class MailModel extends Model
{
    protected $table         = 'MailAEnvoyer';
    protected $primaryKey    = 'idMail';
    protected $returnType    = 'array';
    protected $allowedFields = ['envoye'];

    public function derniers(int $nombre = 20): array
    {
        return $this->orderBy('idMail', 'DESC')->findAll($nombre);
    }
}
