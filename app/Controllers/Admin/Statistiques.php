<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Pdo\StatistiquesPdo;

class Statistiques extends BaseController
{
    public function index(): string
    {
        $stats  = new StatistiquesPdo();
        $annees = $stats->anneesDisponibles();
        $annee  = (int) $this->request->getGet('annee');
        $annee  = in_array($annee, $annees, true) ? $annee : (int) date('Y');

        return view('admin/statistiques', [
            'titre'          => 'Statistiques du Club',
            'annee'          => $annee,
            'annees'         => $annees,
            'totalEnCours'   => $stats->montantTotalAdhesions(),
            'montantAnnee'   => $stats->montantAdhesions($annee),
            'parCategorie'   => $stats->adhesionsParCategorie($annee),
        ]);
    }
}
