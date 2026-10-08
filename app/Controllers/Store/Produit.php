<?php

namespace App\Controllers\Store;

use App\Controllers\BaseController;
use App\Models\ImageModel;
use App\Models\ProduitModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Produit extends BaseController
{
    public function show(int $id): string
    {
        $produits = model(ProduitModel::class);
        $robot    = $produits->fiche($id);

        if ($robot === null) {
            throw PageNotFoundException::forPageNotFound('Ce robot n\'existe pas ou n\'est plus en vente.');
        }

        return view('store/produit', [
            'titre'       => $robot['nom'],
            'description' => mb_substr($robot['description'], 0, 150),
            'robot'       => $robot,
            'images'      => model(ImageModel::class)->duProduit($id),
            'compatibles' => $produits->compatibles($id),
        ]);
    }
}
