<?php

namespace App\Models;

use CodeIgniter\Model;

/** Vue v_EvenementsPresents : événements avec les personnes présentes (lecture seule). */
class VueEvenementsPresentsModel extends Model
{
    protected $table         = 'v_EvenementsPresents';
    protected $primaryKey    = 'idEvenement';
    protected $returnType    = 'array';
    protected $allowedFields = [];

    /** @return array<string, list<array>> titre de l'événement => présents */
    public function parEvenement(): array
    {
        $groupes = [];
        foreach ($this->orderBy('dateDebut')->orderBy('nom')->findAll() as $ligne) {
            $groupes[$ligne['titre']][] = $ligne;
        }

        return $groupes;
    }
}
