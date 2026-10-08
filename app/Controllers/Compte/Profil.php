<?php

namespace App\Controllers\Compte;

use App\Controllers\BaseController;
use App\Models\Pdo\ClubPdo;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use InvalidArgumentException;

/**
 * Profil de l'adhérent connecté et changement de formule (appel Ajax).
 */
class Profil extends BaseController
{
    public function index(): RedirectResponse|string
    {
        $id     = (int) session('idUtilisateur');
        $club   = new ClubPdo();
        $profil = session('role') === 'client' ? $club->profil($id) : null;

        if ($profil === null) {
            return redirect()->to(site_url('/'))->with('erreur', 'Le profil adhérent est réservé aux clients.');
        }

        return view('compte/profil', [
            'titre'        => 'Mon profil',
            'profil'       => $profil,
            'interets'     => $club->interetsDe($id),
            'reservations' => $club->reservationsDe($id),
            'formules'     => $club->formules(),
        ]);
    }

    public function formule(): ResponseInterface
    {
        $donnees = $this->request->getJSON(true) ?? [];

        try {
            $resultat = (new ClubPdo())->changerFormule((int) session('idUtilisateur'), (int) ($donnees['idTarif'] ?? 0));
        } catch (InvalidArgumentException $erreur) {
            return $this->response->setStatusCode(400)->setJSON(['succes' => false, 'message' => $erreur->getMessage(), 'csrf' => csrf_hash()]);
        }

        return $this->response->setJSON([
            'succes'       => true,
            'formule'      => $resultat['formule'],
            'montant'      => $resultat['montant'],
            'montantTexte' => euros($resultat['montant']),
            'csrf'         => csrf_hash(),
        ]);
    }
}
