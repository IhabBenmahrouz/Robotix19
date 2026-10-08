<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Journaliseur;
use App\Models\AnimateurModel;
use App\Models\EvenementModel;
use App\Models\ProduitModel;
use App\Models\ShowroomModel;
use App\Models\TypeEvenementModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Gestion du planning : ajout, modification et suppression des événements.
 */
class Evenements extends BaseController
{
    public function index(): string
    {
        return view('admin/evenements/index', [
            'titre'      => 'Gestion du planning',
            'evenements' => model(EvenementModel::class)->tous(),
            'types'      => model(TypeEvenementModel::class)->liste(),
        ]);
    }

    /** AP3 : l'événement est confié au remplaçant de son animateur. */
    public function remplacer(int $id): RedirectResponse
    {
        $evenement = $this->trouver($id);
        $nouveau   = model(AnimateurModel::class)->remplacerSurEvenement($id);

        if ($nouveau === null) {
            return redirect()->to(site_url('admin/evenements'))->with('erreur', 'L\'animateur de cet événement n\'a pas de remplaçant.');
        }

        $remplacant = model(AnimateurModel::class)->complet($nouveau);
        Journaliseur::noter('remplacement', 'Evenement', $id, '« ' . $evenement['titre'] . ' » confié à ' . $remplacant['prenom'] . ' ' . $remplacant['nom']);

        return redirect()->to(site_url('admin/evenements'))
            ->with('succes', '« ' . $evenement['titre'] . ' » est désormais animé par ' . $remplacant['prenom'] . ' ' . $remplacant['nom'] . '.');
    }

    public function nouveau(): string
    {
        return $this->formulaire(null);
    }

    public function modifier(int $id): string
    {
        return $this->formulaire($this->trouver($id));
    }

    public function creer(): RedirectResponse
    {
        $donnees = $this->donneesValides(null);
        if (is_string($donnees)) {
            return redirect()->back()->withInput()->with('erreur', $donnees);
        }

        $id = (int) model(EvenementModel::class)->insert($donnees);
        Journaliseur::noter('ajout', 'Evenement', $id, 'Ajout de l\'événement « ' . $donnees['titre'] . ' »');

        return redirect()->to(site_url('admin/evenements'))->with('succes', 'Événement ajouté au planning.');
    }

    public function mettreAJour(int $id): RedirectResponse
    {
        $this->trouver($id);
        $donnees = $this->donneesValides($id);
        if (is_string($donnees)) {
            return redirect()->back()->withInput()->with('erreur', $donnees);
        }

        model(EvenementModel::class)->update($id, $donnees);
        Journaliseur::noter('modification', 'Evenement', $id, 'Modification de l\'événement « ' . $donnees['titre'] . ' »');

        return redirect()->to(site_url('admin/evenements'))->with('succes', 'Événement mis à jour.');
    }

    public function supprimer(int $id): RedirectResponse
    {
        $evenement = $this->trouver($id);

        model(EvenementModel::class)->delete($id);
        Journaliseur::noter('suppression', 'Evenement', $id, 'Suppression de l\'événement « ' . $evenement['titre'] . ' »');

        return redirect()->to(site_url('admin/evenements'))->with('succes', 'Événement supprimé.');
    }

    private function trouver(int $id): array
    {
        $evenement = model(EvenementModel::class)->find($id);

        if ($evenement === null) {
            throw PageNotFoundException::forPageNotFound('Événement introuvable.');
        }

        return $evenement;
    }

    private function formulaire(?array $evenement): string
    {
        return view('admin/evenements/formulaire', [
            'titre'     => $evenement === null ? 'Nouvel événement' : 'Modifier l\'événement',
            'evenement' => $evenement,
            'types'     => model(TypeEvenementModel::class)->liste(),
            'animateurs' => model(AnimateurModel::class)->complets(),
            'showrooms' => model(ShowroomModel::class)->orderBy('ville')->findAll(),
            'robots'    => model(ProduitModel::class)->pourCalculateur(),
        ]);
    }

    /**
     * Valide le formulaire et les règles métier.
     *
     * @return array|string données prêtes pour la base, ou message d'erreur
     */
    private function donneesValides(?int $id): array|string
    {
        $formatDate = 'regex_match[/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/]';
        $regles = [
            'titre'       => ['label' => 'Titre', 'rules' => ['required', 'max_length[150]']],
            'description' => ['label' => 'Description', 'rules' => ['permit_empty', 'max_length[1000]']],
            'type'        => ['label' => 'Type', 'rules' => ['required', 'is_not_unique[TypeEvenement.code]']],
            'idAnimateur' => ['label' => 'Animateur', 'rules' => ['permit_empty', 'is_natural_no_zero', 'is_not_unique[Animateur.idUtilisateur]']],
            'dateDebut'   => ['label' => 'Début', 'rules' => ['required', $formatDate]],
            'dateFin'     => ['label' => 'Fin', 'rules' => ['required', $formatDate]],
            'idShowroom'  => ['label' => 'Lieu', 'rules' => ['permit_empty', 'is_natural_no_zero', 'is_not_unique[Showroom.idShowroom]']],
            'idProduit'   => ['label' => 'Robot', 'rules' => ['permit_empty', 'is_natural_no_zero', 'is_not_unique[Produit.idProduit]']],
        ];

        if (! $this->validate($regles)) {
            return implode(' ', $this->validator->getErrors());
        }

        $d     = $this->validator->getValidated();
        $debut = date_sql($d['dateDebut']);
        $fin   = date_sql($d['dateFin']);

        if ($fin <= $debut) { // les dates ISO se comparent comme des chaînes
            return 'La fin doit être postérieure au début.';
        }

        $idShowroom = ($d['idShowroom'] ?? '') !== '' ? (int) $d['idShowroom'] : null;

        if (model(EvenementModel::class)->chevauche($idShowroom, $debut, $fin, $id)) {
            return 'Ce showroom a déjà un événement sur ce créneau.';
        }

        $description = trim((string) ($d['description'] ?? ''));

        return [
            'titre'       => trim($d['titre']),
            'description' => $description === '' ? null : $description,
            'type'        => $d['type'],
            'dateDebut'   => $debut,
            'dateFin'     => $fin,
            'idShowroom'  => $idShowroom,
            'idProduit'   => ($d['idProduit'] ?? '') !== '' ? (int) $d['idProduit'] : null,
            'idAnimateur' => ($d['idAnimateur'] ?? '') !== '' ? (int) $d['idAnimateur'] : null,
        ];
    }
}
