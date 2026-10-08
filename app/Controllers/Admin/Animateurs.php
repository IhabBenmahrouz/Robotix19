<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Journaliseur;
use App\Models\AnimateurModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use InvalidArgumentException;

/**
 * Organisation du club : CRUD des animateurs (adhérents entraîneurs) et gestion du remplacement.
 */
class Animateurs extends BaseController
{
    private AnimateurModel $animateurs;

    public function __construct()
    {
        $this->animateurs = model(AnimateurModel::class);
    }

    public function index(): string
    {
        return view('admin/animateurs/index', [
            'titre'      => 'Animateurs du club',
            'animateurs' => $this->animateurs->complets(),
            'candidats'  => $this->animateurs->candidats(),
        ]);
    }

    public function creer(): RedirectResponse
    {
        $regles = [
            'idUtilisateur' => ['label' => 'Adhérent', 'rules' => ['required', 'is_natural_no_zero']],
            'specialite'    => ['label' => 'Spécialité', 'rules' => ['required', 'min_length[3]', 'max_length[80]']],
            'idRemplacant'  => ['label' => 'Remplaçant', 'rules' => ['permit_empty', 'is_natural_no_zero']],
        ];
        if (! $this->validate($regles)) {
            return redirect()->back()->withInput()->with('erreur', implode(' ', $this->validator->getErrors()));
        }

        $d  = $this->validator->getValidated();
        $id = (int) $d['idUtilisateur'];

        try {
            $this->animateurs->creer($id, $d['specialite'], $this->remplacant($d));
        } catch (InvalidArgumentException $refus) {
            return redirect()->back()->withInput()->with('erreur', $refus->getMessage());
        }

        Journaliseur::noter('ajout', 'Animateur', $id, 'Nouvel animateur : ' . $d['specialite']);

        return redirect()->to(site_url('admin/club/animateurs'))->with('succes', 'Animateur ajouté.');
    }

    public function modifier(int $id): string
    {
        $animateur = $this->trouver($id);

        return view('admin/animateurs/formulaire', [
            'titre'      => 'Modifier un animateur',
            'animateur'  => $animateur,
            'remplacants' => array_filter($this->animateurs->complets(), static fn (array $a): bool => (int) $a['idUtilisateur'] !== $id),
        ]);
    }

    public function mettreAJour(int $id): RedirectResponse
    {
        $this->trouver($id);
        $regles = [
            'specialite'   => ['label' => 'Spécialité', 'rules' => ['required', 'min_length[3]', 'max_length[80]']],
            'idRemplacant' => ['label' => 'Remplaçant', 'rules' => ['permit_empty', 'is_natural_no_zero']],
        ];
        if (! $this->validate($regles)) {
            return redirect()->back()->withInput()->with('erreur', implode(' ', $this->validator->getErrors()));
        }

        $d = $this->validator->getValidated();

        try {
            $this->animateurs->modifier($id, $d['specialite'], $this->remplacant($d));
        } catch (InvalidArgumentException $refus) {
            return redirect()->back()->withInput()->with('erreur', $refus->getMessage());
        }

        Journaliseur::noter('modification', 'Animateur', $id, 'Animateur modifié (spécialité ou remplaçant)');

        return redirect()->to(site_url('admin/club/animateurs'))->with('succes', 'Animateur mis à jour.');
    }

    public function supprimer(int $id): RedirectResponse
    {
        $animateur = $this->trouver($id);
        $this->animateurs->supprimerAnimateur($id);
        Journaliseur::noter('suppression', 'Animateur', $id, 'Retrait de l\'animateur ' . $animateur['prenom'] . ' ' . $animateur['nom']);

        return redirect()->to(site_url('admin/club/animateurs'))
            ->with('succes', 'Animateur retiré : ses événements sont confiés à ' . ($animateur['remplacant'] ?? 'personne') . '.');
    }

    private function trouver(int $id): array
    {
        $animateur = $this->animateurs->complet($id);
        if ($animateur === null) {
            throw PageNotFoundException::forPageNotFound('Animateur introuvable.');
        }

        return $animateur;
    }

    private function remplacant(array $donnees): ?int
    {
        return ($donnees['idRemplacant'] ?? '') !== '' ? (int) $donnees['idRemplacant'] : null;
    }
}
