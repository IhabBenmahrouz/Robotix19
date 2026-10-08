<?php

namespace App\Controllers\Compte;

use App\Controllers\BaseController;
use App\Libraries\LimiteurConnexion;
use App\Models\JetonMotDePasseModel;
use App\Models\UtilisateurModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * « Mot de passe oublié » : demande d'un lien par e-mail, puis choix d'un nouveau mot de passe.
 * Le mail est déposé dans la table MailAEnvoyer (envoi réel hors du périmètre de la démo).
 */
class MotDePasse extends BaseController
{
    private const CONFIRMATION = 'Si un compte actif correspond à cette adresse, un lien de réinitialisation valable une heure vient de lui être envoyé.';

    public function oublie(): string
    {
        return view('compte/mot_de_passe_oublie', ['titre' => 'Mot de passe oublié']);
    }

    public function demander(): RedirectResponse
    {
        $email = strtolower(trim((string) $this->request->getPost('email')));
        if (! $this->validateData(['email' => $email], ['email' => ['label' => 'E-mail', 'rules' => 'required|valid_email|max_length[150]']])) {
            return redirect()->back()->withInput()->with('erreur', 'Saisissez une adresse e-mail valide.');
        }

        $utilisateur = model(UtilisateurModel::class)->where('email', $email)->where('actif', 1)->first();

        // Même réponse que le compte existe ou non : on ne révèle pas quelles adresses sont inscrites
        if ($utilisateur !== null) {
            $jeton = model(JetonMotDePasseModel::class)->creer((int) $utilisateur['idUtilisateur']);
            if ($jeton !== null) {
                db_connect()->table('MailAEnvoyer')->insert([
                    'destinataire' => $utilisateur['email'],
                    'objet'        => 'Réinitialisation de votre mot de passe Robotix',
                    'corps'        => 'Bonjour ' . $utilisateur['prenom'] . ', pour choisir un nouveau mot de passe, ouvrez ce lien (valable '
                                      . JetonMotDePasseModel::DUREE_MINUTES . ' minutes, une seule fois) : ' . site_url('compte/mot-de-passe/' . $jeton)
                                      . ' — Si vous n\'êtes pas à l\'origine de cette demande, ignorez ce message.',
                ]);
            }
        }

        return redirect()->to(site_url('compte/connexion'))->with('succes', self::CONFIRMATION);
    }

    public function formulaire(string $jeton): string|RedirectResponse
    {
        if (model(JetonMotDePasseModel::class)->valide($jeton) === null) {
            return $this->lienInvalide();
        }

        return view('compte/mot_de_passe_nouveau', ['titre' => 'Nouveau mot de passe', 'jeton' => $jeton]);
    }

    public function changer(string $jeton): RedirectResponse
    {
        $jetons = model(JetonMotDePasseModel::class);
        $valide = $jetons->valide($jeton);
        if ($valide === null) {
            return $this->lienInvalide();
        }

        if (! $this->validate(Auth::REGLES_MOT_DE_PASSE)) {
            return redirect()->back()->with('erreur', implode(' ', $this->validator->getErrors()));
        }

        $jetons->utiliser($valide, (string) $this->request->getPost('motDePasse'));
        // Le compte n'est plus bloqué par d'anciennes tentatives échouées
        (new LimiteurConnexion())->noter($valide['email'], $this->request->getIPAddress(), true);

        return redirect()->to(site_url('compte/connexion'))->with('succes', 'Votre mot de passe a été modifié. Vous pouvez vous connecter.');
    }

    private function lienInvalide(): RedirectResponse
    {
        return redirect()->to(site_url('compte/mot-de-passe-oublie'))
            ->with('erreur', 'Ce lien n\'est plus valable (déjà utilisé ou expiré). Faites une nouvelle demande.');
    }
}
