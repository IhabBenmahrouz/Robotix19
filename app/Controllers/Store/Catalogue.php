<?php

namespace App\Controllers\Store;

use App\Controllers\BaseController;
use App\Models\CategorieModel;
use App\Models\MarqueModel;
use App\Models\ProduitModel;

class Catalogue extends BaseController
{
    public function index(): string
    {
        $filtres = [
            'categorie' => (int) $this->request->getGet('categorie'),
            'marque'    => (int) $this->request->getGet('marque'),
            'prixMax'   => (int) $this->request->getGet('prixMax'),
            'q'         => trim((string) $this->request->getGet('q')),
            'tri'       => (string) ($this->request->getGet('tri') ?? 'nouveautes'),
        ];

        return view('store/catalogue', [
            'titre'      => 'Robotix Store',
            'robots'     => model(ProduitModel::class)->catalogue($filtres),
            'filtres'    => $filtres,
            'categories' => model(CategorieModel::class)->orderBy('libelle')->findAll(),
            'marques'    => model(MarqueModel::class)->orderBy('nom')->findAll(),
        ]);
    }
}
