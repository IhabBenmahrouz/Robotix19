<?php

namespace App\Models;

use CodeIgniter\Model;

/** Vue v_AdherentsRoles : adhérents entraîneurs et joueurs (lecture seule). */
class VueAdherentsRolesModel extends Model
{
    protected $table         = 'v_AdherentsRoles';
    protected $primaryKey    = 'idUtilisateur';
    protected $returnType    = 'array';
    protected $allowedFields = [];
}
