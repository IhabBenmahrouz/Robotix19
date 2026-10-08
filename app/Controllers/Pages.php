<?php

namespace App\Controllers;

use App\Libraries\CalendrierDemo;
use App\Libraries\Tarifs;
use App\Models\EvenementModel;
use App\Models\ProduitModel;
use App\Models\ShowroomModel;
use App\Models\TarifModel;

/**
 * Pages vitrine de Robotix.
 */
class Pages extends BaseController
{
    public function accueil(): string
    {
        return view('pages/accueil', [
            'titre'       => 'Accueil',
            'description' => 'Robotix : robots humanoïdes pour les particuliers, actualités et démonstrations.',
            'phares'      => model(ProduitModel::class)->phares(4),
            'evenements'  => model(EvenementModel::class)->prochains(3),
            'types'       => EvenementModel::TYPES,
        ]);
    }

    /** Crédits des vraies photos de robots (licences libres Wikimedia Commons). */
    public function credits(): string
    {
        $photos = db_connect()->table('Image i')
            ->select('i.fichier, i.legende, i.credit, i.licence, i.source, p.nom AS robot')
            ->join('Produit p', 'p.idProduit = i.idProduit')
            ->where('i.credit IS NOT NULL', null, false)
            ->orderBy('p.nom')->orderBy('i.numOrdre')
            ->get()->getResultArray();

        return view('pages/credits', ['titre' => 'Crédits photos', 'photos' => $photos]);
    }

    /** Présentation du projet pour le jury : contexte, vrai/fictif, comptes et parcours de démonstration. */
    public function aPropos(): string
    {
        $db        = db_connect();
        $objetsSql = $db->query("SELECT
                SUM(CASE WHEN type = 'P'  AND name LIKE 'ps[_]%'  THEN 1 ELSE 0 END) AS procedures,
                SUM(CASE WHEN type = 'TR' AND name LIKE 'trg[_]%' THEN 1 ELSE 0 END) AS declencheurs,
                SUM(CASE WHEN type = 'V'  AND name LIKE 'v[_]%'   THEN 1 ELSE 0 END) AS vues,
                SUM(CASE WHEN type = 'U' THEN 1 ELSE 0 END) AS tables
            FROM sys.objects")->getRowArray();

        return view('pages/a_propos', [
            'titre'       => 'À propos du projet',
            'description' => 'Robotix19 : projet BTS SIO réalisé avec CodeIgniter 4 et SQL Server, présentation pour le jury.',
            'chiffres'    => [
                'robots réels'       => $db->table('Produit')->countAllResults(),
                'marques réelles'    => $db->table('Marque')->countAllResults(),
                'tables SQL Server'  => (int) $objetsSql['tables'],
                'procédures stockées' => (int) $objetsSql['procedures'],
                'déclencheurs'       => (int) $objetsSql['declencheurs'],
                'vues SQL'           => (int) $objetsSql['vues'],
            ],
            'calendrier'  => CalendrierDemo::courant(),
        ]);
    }

    public function news(): string
    {
        return view('pages/news', ['titre' => 'Robotix News']);
    }

    public function tarifs(): string
    {
        $robots = array_map(static fn (array $r): array => [
            'idProduit' => (int) $r['idProduit'],
            'nom'       => $r['nom'],
            'prixHt'    => (float) $r['prixHt'],
            'tauxTva'   => (float) $r['tauxTva'],
        ], model(ProduitModel::class)->pourCalculateur());

        return view('pages/tarifs', [
            'titre'        => 'Tarifs',
            'gammes'       => model(ProduitModel::class)->gammes(),
            'options'      => model(TarifModel::class)->options(),
            'financements' => Tarifs::financements(),
            'robots'       => $robots,
            'robotChoisi'  => (int) $this->request->getGet('robot'),
        ]);
    }

    public function evenements(): string
    {
        $mois = (string) $this->request->getGet('mois');

        return view('pages/evenements', [
            'titre'     => 'Événements',
            'mois'      => preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mois) ? $mois : date('Y-m'),
            'types'     => EvenementModel::TYPES,
            'showrooms' => model(ShowroomModel::class)->orderBy('ville')->findAll(),
        ]);
    }

    public function showrooms(): string
    {
        return view('pages/showrooms', [
            'titre'     => 'Nos showrooms',
            'showrooms' => model(ShowroomModel::class)->orderBy('ville')->findAll(),
        ]);
    }
}
