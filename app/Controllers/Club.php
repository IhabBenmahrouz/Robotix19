<?php

namespace App\Controllers;

use App\Libraries\ProceduresAp3;
use App\Models\AnimateurModel;
use App\Models\EvenementModel;
use App\Models\InscriptionModel;
use App\Models\MembreModel;
use App\Models\VueEvenementsPresentsModel;
use CodeIgniter\HTTP\RedirectResponse;
use DomainException;

/**
 * Club Robotix : page invité, espace membre (joueur) et espace animateur (entraîneur).
 */
class Club extends BaseController
{
    /** Page invité : présentation du club. */
    public function index(): string
    {
        $heures = array_sum(array_map('floatval', array_column((new ProceduresAp3())->heuresEntrainement(), 'heures')));

        return view('club/index', [
            'titre'      => 'Club Robotix',
            'animateurs' => model(AnimateurModel::class)->complets(),
            'ateliers'   => array_slice(model(EvenementModel::class)->aVenirDetailles('atelier'), 0, 3),
            'nbMembres'  => model(MembreModel::class)->countAllResults(),
            'heures'     => $heures,
            'presences'  => model(VueEvenementsPresentsModel::class)->countAllResults(),
        ]);
    }

    /** Page joueur : liste des joueurs, événements à venir, événements suivis. */
    public function membres(): string
    {
        $id      = (int) session('idUtilisateur');
        $suivis  = (new ProceduresAp3())->evenementsSuivis($id);
        $inscrit = array_map('intval', array_column($suivis, 'idEvenement'));

        return view('club/membres', [
            'titre'      => 'Espace membre',
            'joueurs'    => model(MembreModel::class)->complets(),
            'evenements' => model(EvenementModel::class)->aVenirDetailles(),
            'suivis'     => $suivis,
            'inscrit'    => $inscrit,
        ]);
    }

    public function inscrire(): RedirectResponse
    {
        try {
            model(InscriptionModel::class)->inscrire((int) session('idUtilisateur'), (int) $this->request->getPost('idEvenement'));
        } catch (DomainException $refus) {
            return redirect()->to(site_url('club/membres'))->with('erreur', $refus->getMessage());
        }

        return redirect()->to(site_url('club/membres'))->with('succes', 'Inscription confirmée : un mail de confirmation vous a été envoyé.');
    }

    /** Page entraîneur : ses événements et la saisie des présences. */
    public function animateur(): string
    {
        $id         = (int) session('idUtilisateur');
        $evenements = model(AnimateurModel::class)->evenementsAnimes($id);
        $inscriptions = model(InscriptionModel::class);

        foreach ($evenements as &$evenement) {
            $evenement['inscrits'] = $inscriptions->inscritsDe((int) $evenement['idEvenement']);
        }
        unset($evenement);

        return view('club/animateur', [
            'titre'      => 'Espace animateur',
            'animateur'  => model(AnimateurModel::class)->complet($id),
            'evenements' => $evenements,
        ]);
    }

    public function pointer(int $idEvenement): RedirectResponse
    {
        if (! model(AnimateurModel::class)->peutPointer((int) session('idUtilisateur'), $idEvenement)) {
            return redirect()->to(site_url('club/animateur'))->with('erreur', 'Vous n\'animez pas cet événement.');
        }

        $presents     = (array) $this->request->getPost('present');
        $travaux      = (array) $this->request->getPost('travail');
        $inscriptions = model(InscriptionModel::class);

        foreach ($inscriptions->inscritsDe($idEvenement) as $inscrit) {
            $idMembre = (int) $inscrit['idMembre'];
            $valeur   = $presents[$idMembre] ?? '';
            $inscriptions->pointer(
                $idMembre,
                $idEvenement,
                $valeur === '' ? null : $valeur === '1',
                isset($travaux[$idMembre]) ? (string) $travaux[$idMembre] : null,
            );
        }

        return redirect()->to(site_url('club/animateur') . '#evenement-' . $idEvenement)->with('succes', 'Présences et travaux enregistrés.');
    }
}
