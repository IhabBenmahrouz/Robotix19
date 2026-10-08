<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ProceduresAp3;
use App\Models\MailModel;
use App\Models\MembreModel;
use App\Models\ReunionModel;
use App\Models\VueAdherentsRolesModel;
use App\Models\VueEvenementsPresentsModel;

/**
 * Organisation du club : résultats des procédures stockées, des vues et des déclencheurs (AP3).
 */
class Rapports extends BaseController
{
    public function index(): string
    {
        $ps      = new ProceduresAp3();
        $membres = model(MembreModel::class)->complets();
        $requete = $this->request;

        $parametres = [
            'annee'      => $this->entier($requete->getGet('annee'), (int) date('Y'), 2000, 2100),
            'date'       => $this->date($requete->getGet('date'), date('Y-m-d')),
            'idAdherent' => $this->entier($requete->getGet('idAdherent'), (int) ($membres[0]['idUtilisateur'] ?? 0)),
            'idMembre'   => $this->entier($requete->getGet('idMembre'), (int) ($membres[0]['idUtilisateur'] ?? 0)),
            'debut'      => $this->date($requete->getGet('debut'), date('Y') . '-01-01'),
            'fin'        => $this->date($requete->getGet('fin'), date('Y-m-d')),
        ];

        return view('admin/rapports', [
            'titre'       => 'Rapports du club',
            'p'           => $parametres,
            'membres'     => $membres,
            'reunions'    => model(ReunionModel::class)->avecDetails(),
            'renouveles'  => $ps->adherentsRenouveles($parametres['annee']),
            'ordreDuJour' => $ps->ordreDuJour($parametres['date']),
            'suivis'      => $ps->evenementsSuivis($parametres['idAdherent']),
            'nbEntre'     => $ps->nbEvenementsEntreDates($parametres['idMembre'], $parametres['debut'], $parametres['fin']),
            'heures'      => $ps->heuresEntrainement(),
            'roles'       => model(VueAdherentsRolesModel::class)->orderBy('role')->orderBy('nom')->findAll(),
            'presents'    => model(VueEvenementsPresentsModel::class)->parEvenement(),
            'mails'       => model(MailModel::class)->derniers(10),
            'historique'  => db_connect()->table('HistoriqueAdhesion')->orderBy('dateHistorisation', 'DESC')->get()->getResultArray(),
        ]);
    }

    private function entier(mixed $valeur, int $defaut, int $min = 1, int $max = PHP_INT_MAX): int
    {
        $nombre = filter_var($valeur, FILTER_VALIDATE_INT);

        return $nombre === false || $nombre < $min || $nombre > $max ? $defaut : $nombre;
    }

    private function date(mixed $valeur, string $defaut): string
    {
        $valeur = (string) $valeur;
        $date   = \DateTimeImmutable::createFromFormat('!Y-m-d', $valeur);

        return $date !== false && $date->format('Y-m-d') === $valeur ? $valeur : $defaut;
    }
}
