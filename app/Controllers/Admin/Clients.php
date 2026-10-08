<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Contact;
use App\Libraries\Journaliseur;
use App\Models\Pdo\ClubPdo;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use DomainException;

/**
 * Gestion des adhérents du Club Robotix (accès aux données avec PDO).
 */
class Clients extends BaseController
{
    private ClubPdo $club;

    public function __construct()
    {
        $this->club = new ClubPdo();
    }

    public function index(): string
    {
        $categorie = (int) $this->request->getGet('categorie');

        return view('admin/clients/index', [
            'titre'      => 'Adhérents du Club',
            'categories' => $this->club->categoriesAge(),
            'categorie'  => $categorie,
            'adherents'  => $this->club->adherents($categorie > 0 ? $categorie : null),
        ]);
    }

    public function modifier(int $id): string
    {
        return view('admin/clients/formulaire', ['titre' => 'Modifier un adhérent', 'adherent' => $this->trouver($id)]);
    }

    public function mettreAJour(int $id): RedirectResponse
    {
        $adherent = $this->trouver($id);
        $regles   = [
            'nom'           => ['label' => 'Nom', 'rules' => ['required', 'regex_match[' . Contact::MOTIFS['nom'] . ']']],
            'prenom'        => ['label' => 'Prénom', 'rules' => ['required', 'regex_match[' . Contact::MOTIFS['nom'] . ']']],
            'email'         => ['label' => 'E-mail', 'rules' => ['required', 'valid_email', 'max_length[150]']],
            'telephone'     => ['label' => 'Téléphone', 'rules' => ['required', 'regex_match[' . Contact::MOTIFS['telephone'] . ']']],
            'dateNaissance' => ['label' => 'Date de naissance', 'rules' => ['required', 'valid_date[Y-m-d]']],
            'actif'         => ['label' => 'Actif', 'rules' => ['permit_empty', 'in_list[0,1]']],
        ];

        if (! $this->validate($regles)) {
            return redirect()->back()->withInput()->with('erreur', implode(' ', $this->validator->getErrors()));
        }

        try {
            $this->club->modifier($id, $this->validator->getValidated());
        } catch (DomainException $refus) {
            return redirect()->back()->withInput()->with('erreur', $refus->getMessage());
        }

        Journaliseur::noter('modification', 'Client', $id, 'Modification de l\'adhérent ' . $adherent['prenom'] . ' ' . $adherent['nom']);

        return redirect()->to(site_url('admin/clients'))->with('succes', 'Adhérent mis à jour.');
    }

    public function supprimer(int $id): RedirectResponse
    {
        $adherent = $this->trouver($id);

        if (! $this->club->supprimer($id)) {
            return redirect()->back()->with('erreur', 'Ce client a passé des commandes : désactivez son compte au lieu de le supprimer.');
        }

        if ($adherent['photo'] !== null && is_file(FCPATH . 'uploads/clients/' . $adherent['photo'])) {
            unlink(FCPATH . 'uploads/clients/' . $adherent['photo']);
        }
        Journaliseur::noter('suppression', 'Client', $id, 'Suppression de l\'adhérent ' . $adherent['prenom'] . ' ' . $adherent['nom']);

        return redirect()->to(site_url('admin/clients'))->with('succes', 'Adhérent supprimé.');
    }

    private function trouver(int $id): array
    {
        $adherent = $this->club->profil($id);

        if ($adherent === null) {
            throw PageNotFoundException::forPageNotFound('Adhérent introuvable.');
        }

        return $adherent;
    }
}
