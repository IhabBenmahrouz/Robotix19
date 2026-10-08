<?php

namespace App\Controllers;

use App\Models\MessageContactModel;
use App\Models\ProduitModel;
use CodeIgniter\HTTP\RedirectResponse;

class Contact extends BaseController
{
    /** Mêmes contrôles que validation.js, répétés côté serveur (le JavaScript peut être désactivé). */
    public const MOTIFS = [
        'nom'       => "/^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,50}$/u",
        'telephone' => '/^0[1-9](?:[ .-]?\d{2}){4}$/',
    ];

    public function index(): string
    {
        return view('pages/contact', [
            'titre'       => 'Contact',
            'robots'      => model(ProduitModel::class)->pourCalculateur(),
            'objets'      => MessageContactModel::OBJETS,
            'objetChoisi' => (string) $this->request->getGet('objet'),
            'robotChoisi' => (int) $this->request->getGet('robot'),
        ]);
    }

    public function envoyer(): RedirectResponse
    {
        $regles = [
            'nom'       => ['label' => 'Nom', 'rules' => ['required', 'regex_match[' . self::MOTIFS['nom'] . ']']],
            'email'     => ['label' => 'E-mail', 'rules' => ['required', 'valid_email', 'max_length[150]']],
            'telephone' => ['label' => 'Téléphone', 'rules' => ['permit_empty', 'regex_match[' . self::MOTIFS['telephone'] . ']']],
            'objet'     => ['label' => 'Objet', 'rules' => ['required', 'in_list[' . implode(',', array_keys(MessageContactModel::OBJETS)) . ']']],
            'idProduit' => ['label' => 'Robot', 'rules' => ['permit_empty', 'is_natural_no_zero', 'is_not_unique[Produit.idProduit]']],
            'message'   => ['label' => 'Message', 'rules' => ['required', 'min_length[20]', 'max_length[2000]']],
            'rgpd'      => ['label' => 'Consentement', 'rules' => ['required'], 'errors' => ['required' => 'Merci d\'accepter le traitement de vos données.']],
        ];

        if (! $this->validate($regles)) {
            return redirect()->back()->withInput()->with('erreur', 'Le formulaire contient des erreurs : vérifiez les champs signalés.');
        }

        $donnees = $this->validator->getValidated();

        model(MessageContactModel::class)->insert([
            'nom'       => trim($donnees['nom']),
            'email'     => strtolower(trim($donnees['email'])),
            'telephone' => ($donnees['telephone'] ?? '') !== '' ? $donnees['telephone'] : null,
            'objet'     => $donnees['objet'],
            'idProduit' => ($donnees['idProduit'] ?? '') !== '' ? (int) $donnees['idProduit'] : null,
            'message'   => trim($donnees['message']),
        ]);

        return redirect()->to(site_url('contact'))
            ->with('succes', 'Merci ! Votre message a bien été envoyé, nous vous répondrons sous 48 heures.');
    }
}
