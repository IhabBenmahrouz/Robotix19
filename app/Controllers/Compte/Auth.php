<?php

namespace App\Controllers\Compte;

use App\Controllers\BaseController;
use App\Controllers\Contact;
use App\Libraries\LimiteurConnexion;
use App\Libraries\PanierReservations;
use App\Models\Pdo\ClubPdo;
use App\Models\UtilisateurModel;
use CodeIgniter\HTTP\RedirectResponse;
use DomainException;
use Throwable;

class Auth extends BaseController
{
    /** Règles du mot de passe, communes à l'inscription et à la réinitialisation. */
    public const REGLES_MOT_DE_PASSE = [
        'motDePasse'   => ['label' => 'Mot de passe', 'rules' => ['required', 'regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/]'],
                           'errors' => ['regex_match' => '8 caractères minimum, avec une majuscule, une minuscule et un chiffre.']],
        'confirmation' => ['label' => 'Confirmation', 'rules' => ['required', 'matches[motDePasse]'],
                           'errors' => ['matches' => 'Les deux mots de passe sont différents.']],
    ];

    public function connexion(): string
    {
        return view('compte/connexion', ['titre' => 'Connexion']);
    }

    public function seConnecter(): RedirectResponse
    {
        // Champ « E-mail ou pseudo » (l'ancien nom de champ « email » reste accepté)
        $identifiant  = (string) ($this->request->getPost('identifiant') ?? $this->request->getPost('email'));
        $ip           = $this->request->getIPAddress();
        $limiteur     = new LimiteurConnexion();

        // Trop d'échecs récents : refus avant même de vérifier le mot de passe (protection contre la force brute)
        $minutes = $limiteur->minutesDeBlocage($identifiant, $ip);
        if ($minutes > 0) {
            return redirect()->back()->withInput()->with('erreur', "Trop de tentatives échouées. Réessayez dans $minutes minute(s).");
        }

        $utilisateurs = model(UtilisateurModel::class);
        $utilisateur  = $utilisateurs->trouverParIdentifiant($identifiant);
        $motDePasse   = (string) $this->request->getPost('motDePasse');

        if ($utilisateur === null || ! (bool) $utilisateur['actif'] || ! password_verify($motDePasse, $utilisateur['motDePasse'])) {
            $limiteur->noter($identifiant, $ip, false);
            $restants = $limiteur->essaisRestants($identifiant);
            $message  = 'Identifiant ou mot de passe incorrect.';
            if ($restants === 0) {
                $message .= ' Connexion bloquée pendant ' . LimiteurConnexion::FENETRE_MINUTES . ' minutes.';
            } elseif ($restants <= 2) {
                $message .= " Encore $restants essai(s) avant un blocage temporaire.";
            }

            return redirect()->back()->withInput()->with('erreur', $message);
        }

        $limiteur->noter($identifiant, $ip, true);

        $id   = (int) $utilisateur['idUtilisateur'];
        $role = $utilisateurs->role($id);
        $this->ouvrirSession($utilisateur, $role, $utilisateurs->profilsClub($id));

        // Redirection : la page demandée avant la connexion, sinon l'espace correspondant au rôle
        $destination = session('redirection') ?? $this->espaceParDefaut($role, session('profilClub'));
        session()->remove('redirection');

        return redirect()->to($destination)->with('succes', 'Bonjour ' . $utilisateur['prenom'] . ' !');
    }

    private function espaceParDefaut(string $role, array $profilClub): string
    {
        return match (true) {
            $role === 'admin'                      => site_url('admin/club/rapports'),
            in_array('animateur', $profilClub, true) => site_url('club/animateur'),
            in_array('membre', $profilClub, true)    => site_url('club/membres'),
            $role === 'client'                     => site_url('compte/profil'),
            default                                => site_url('/'),
        };
    }

    public function inscription(): string
    {
        $club = new ClubPdo();

        return view('compte/inscription', [
            'titre'         => 'Adhérer au Club Robotix',
            'formules'      => $club->formules(),
            'interets'      => $club->categoriesProduit(),
            'categoriesAge' => $club->categoriesAge(),
        ]);
    }

    public function inscrire(): RedirectResponse
    {
        // is_unique s'appuie sur la collation de SQL Server, insensible à la casse :
        // « CLIENT@Robotix.test » est donc reconnu comme doublon de « client@robotix.test ».
        $regles = [
            'nom'           => ['label' => 'Nom', 'rules' => ['required', 'regex_match[' . Contact::MOTIFS['nom'] . ']']],
            'prenom'        => ['label' => 'Prénom', 'rules' => ['required', 'regex_match[' . Contact::MOTIFS['nom'] . ']']],
            'email'         => ['label' => 'E-mail', 'rules' => ['required', 'valid_email', 'max_length[150]', 'is_unique[Utilisateur.email]'],
                                'errors' => ['is_unique' => 'Un compte existe déjà avec cet e-mail.']],
            'telephone'     => ['label' => 'Téléphone', 'rules' => ['required', 'regex_match[' . Contact::MOTIFS['telephone'] . ']']],
            'adresse'       => ['label' => 'Adresse', 'rules' => ['required', 'min_length[5]', 'max_length[120]']],
            'codePostal'    => ['label' => 'Code postal', 'rules' => ['required', 'regex_match[/^\d{5}$/]']],
            'ville'         => ['label' => 'Ville', 'rules' => ['required', 'regex_match[' . str_replace('{2,50}', '{2,80}', Contact::MOTIFS['nom']) . ']']],
            'dateNaissance' => ['label' => 'Date de naissance', 'rules' => ['required', 'valid_date[Y-m-d]']],
            'idTarif'       => ['label' => 'Formule', 'rules' => ['required', 'is_natural_no_zero', 'is_not_unique[Tarif.idTarif]'],
                                'errors' => ['is_not_unique' => 'Choisissez une formule proposée.']],
            'interets.*'    => ['label' => 'Centres d\'intérêt', 'rules' => ['permit_empty', 'is_natural_no_zero']],
        ] + self::REGLES_MOT_DE_PASSE;

        $photo     = $this->request->getFile('photo');
        $avecPhoto = $photo !== null && $photo->getError() !== UPLOAD_ERR_NO_FILE;
        if ($avecPhoto) {
            $regles['photo'] = ['label' => 'Photo', 'rules' => [
                'uploaded[photo]', 'is_image[photo]', 'mime_in[photo,image/jpeg,image/png,image/webp]',
                'ext_in[photo,jpg,jpeg,png,webp]', 'max_size[photo,2048]', 'max_dims[photo,4000,4000]',
            ]];
        }

        if (! $this->validate($regles)) {
            return redirect()->back()->withInput()->with('erreur', 'Merci de corriger les champs signalés.');
        }

        $d         = $this->validator->getValidated();
        $nomPhoto  = null;
        $dossier   = FCPATH . 'uploads/clients';

        if ($avecPhoto) {
            $nomPhoto = $photo->getRandomName();
            $photo->move($dossier, $nomPhoto);
        }

        try {
            $id = (new ClubPdo())->inscrire([
                'nom' => $d['nom'], 'prenom' => $d['prenom'], 'email' => $d['email'], 'motDePasse' => $d['motDePasse'],
                'telephone' => $d['telephone'], 'adresse' => $d['adresse'], 'codePostal' => $d['codePostal'],
                'ville' => $d['ville'], 'dateNaissance' => $d['dateNaissance'], 'idTarif' => (int) $d['idTarif'],
                'interets' => (array) $this->request->getPost('interets'), 'photo' => $nomPhoto,
            ]);
        } catch (DomainException $refus) {
            $this->supprimerPhoto($dossier, $nomPhoto);

            return redirect()->back()->withInput()->with('erreur', $refus->getMessage());
        } catch (Throwable $erreur) {
            log_message('error', 'Inscription : ' . $erreur->getMessage());
            $this->supprimerPhoto($dossier, $nomPhoto);

            return redirect()->back()->withInput()->with('erreur', 'L\'inscription a échoué, veuillez réessayer.');
        }

        $this->ouvrirSession(['idUtilisateur' => $id, 'nom' => trim($d['nom']), 'prenom' => trim($d['prenom'])], 'client');

        return redirect()->to(site_url('compte/profil'))->with('succes', 'Bienvenue au Club Robotix, ' . trim($d['prenom']) . ' !');
    }

    private function supprimerPhoto(string $dossier, ?string $nomPhoto): void
    {
        if ($nomPhoto !== null && is_file($dossier . '/' . $nomPhoto)) {
            unlink($dossier . '/' . $nomPhoto);
        }
    }


    public function deconnexion(): RedirectResponse
    {
        // Le panier de réservations est annulé et vidé à la déconnexion
        session()->remove(['idUtilisateur', 'nom', 'prenom', 'role', 'profilClub', PanierReservations::CLE_SESSION]);
        session()->regenerate(true);

        return redirect()->to(site_url('/'))->with('succes', 'Vous êtes déconnecté. À bientôt !');
    }

    private function ouvrirSession(array $utilisateur, string $role, array $profilClub = []): void
    {
        session()->regenerate(); // nouvel identifiant de session : protège contre la fixation de session
        session()->set([
            'idUtilisateur' => (int) $utilisateur['idUtilisateur'],
            'nom'           => $utilisateur['nom'],
            'prenom'        => $utilisateur['prenom'],
            'role'          => $role,
            'profilClub'    => $profilClub,
        ]);
    }
}
