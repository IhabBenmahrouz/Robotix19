<?php

namespace App\Controllers;

use App\Models\EvenementModel;
use App\Models\ShowroomModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Données JSON lues par calendrier.js et carte.js.
 */
class Api extends BaseController
{
    use ResponseTrait;

    protected $format = 'json';

    public function evenements(): ResponseInterface
    {
        $mois = (string) ($this->request->getGet('mois') ?? date('Y-m'));

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mois)) {
            return $this->failValidationErrors('Paramètre « mois » invalide : format attendu AAAA-MM.');
        }

        $evenements = model(EvenementModel::class)->duMois($mois);

        return $this->respond(array_map([$this, 'formaterEvenement'], $evenements));
    }

    public function showrooms(): ResponseInterface
    {
        $showrooms = model(ShowroomModel::class)->orderBy('ville')->findAll();

        return $this->respond(array_map(static fn (array $s): array => [
            'id'         => (int) $s['idShowroom'],
            'nom'        => $s['nom'],
            'adresse'    => $s['adresse'],
            'codePostal' => $s['codePostal'],
            'ville'      => $s['ville'],
            'latitude'   => (float) $s['latitude'],
            'longitude'  => (float) $s['longitude'],
            'telephone'  => $s['telephone'],
            'horaires'   => $s['horaires'],
        ], $showrooms));
    }

    private function formaterEvenement(array $e): array
    {
        $idProduit = $e['idProduit'] === null ? null : (int) $e['idProduit'];

        return [
            'id'          => (int) $e['idEvenement'],
            'titre'       => $e['titre'],
            'description' => $e['description'],
            'type'        => $e['type'],
            'typeLibelle' => EvenementModel::TYPES[$e['type']] ?? $e['type'],
            'debut'       => date('Y-m-d\TH:i:s', strtotime(substr($e['dateDebut'], 0, 19))),
            'fin'         => date('Y-m-d\TH:i:s', strtotime(substr($e['dateFin'], 0, 19))),
            'idShowroom'  => $e['idShowroom'] === null ? null : (int) $e['idShowroom'],
            'showroom'    => $e['showroom'],
            'ville'       => $e['ville'],
            'idProduit'   => $idProduit,
            'produit'     => $e['produit'],
            'urlProduit'  => $idProduit === null ? null : site_url('store/robot/' . $idProduit),
        ];
    }
}
