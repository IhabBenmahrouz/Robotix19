<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Tarifs du site : formules d'adhésion au Club Robotix et options de service.
 */
class TarifModel extends Model
{
    protected $table         = 'Tarif';
    protected $primaryKey    = 'idTarif';
    protected $returnType    = 'array';
    protected $allowedFields = ['code', 'libelle', 'famille', 'mode', 'valeur', 'description', 'actif'];

    /** Options de service au format du calculateur (type = mode). */
    public function options(): array
    {
        $lignes = $this->where('famille', 'option')->where('actif', 1)->orderBy('idTarif')->findAll();

        return array_map(static fn (array $t): array => [
            'code'        => $t['code'],
            'libelle'     => $t['libelle'],
            'type'        => $t['mode'],
            'valeur'      => (float) $t['valeur'],
            'description' => $t['description'],
        ], $lignes);
    }

    /** Formules d'adhésion, de la moins chère à la plus chère. */
    public function formules(): array
    {
        $lignes = $this->where('famille', 'formule')->where('actif', 1)->orderBy('valeur')->findAll();

        return array_map(static fn (array $t): array => [
            'idTarif'     => (int) $t['idTarif'],
            'code'        => $t['code'],
            'libelle'     => $t['libelle'],
            'valeur'      => (float) $t['valeur'],
            'description' => $t['description'],
        ], $lignes);
    }
}
