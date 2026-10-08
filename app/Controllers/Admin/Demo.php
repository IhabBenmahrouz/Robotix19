<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CalendrierDemo;
use App\Libraries\Journaliseur;
use App\Libraries\PanierReservations;
use App\Models\UtilisateurModel;
use CodeIgniter\HTTP\RedirectResponse;
use Config\Database;
use Throwable;

/**
 * Jeu de démonstration : état du calendrier de démo et remise à zéro des données (seeder).
 */
class Demo extends BaseController
{
    private const ADMIN_DEMO = 'admin@robotix.test';

    public function index(): string
    {
        $db = db_connect();

        return view('admin/demo', [
            'titre'      => 'Jeu de démonstration',
            'calendrier' => CalendrierDemo::courant(),
            'derniere'   => CalendrierDemo::derniereReinitialisation(),
            'aJour'      => CalendrierDemo::courant()->semaines() === CalendrierDemo::semainesDepuisReference(date('Y-m-d')),
            'compteurs'  => [
                'Utilisateurs' => $db->table('Utilisateur')->countAllResults(),
                'Événements'   => $db->table('Evenement')->countAllResults(),
                'Réservations' => $db->table('Panier')->countAllResults(),
                'Messages'     => $db->table('MessageContact')->countAllResults(),
            ],
        ]);
    }

    public function reinitialiser(): RedirectResponse
    {
        if ($this->request->getPost('confirmation') !== 'REINITIALISER') {
            return redirect()->to(site_url('admin/demo'))->with('erreur', 'Tapez REINITIALISER pour confirmer la remise à zéro.');
        }

        $db = db_connect();
        $db->transException(true)->transStart(); // tout ou rien : en cas d'erreur la base reste intacte
        try {
            Database::seeder()->setSilent(true)->call('RobotixSeeder');
            $db->transComplete();
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'Réinitialisation de la démo : ' . $e->getMessage());

            return redirect()->to(site_url('admin/demo'))->with('erreur', 'La réinitialisation a échoué : les données n\'ont pas été modifiées.');
        } finally {
            $db->transException(false);
        }

        // Les comptes ont été recréés : la session repasse sur l'administrateur de démo
        $admin = model(UtilisateurModel::class)->where('email', self::ADMIN_DEMO)->first();
        session()->regenerate();
        session()->remove(PanierReservations::CLE_SESSION);
        session()->set([
            'idUtilisateur' => (int) $admin['idUtilisateur'],
            'nom'           => $admin['nom'],
            'prenom'        => $admin['prenom'],
            'role'          => 'admin',
            'profilClub'    => model(UtilisateurModel::class)->profilsClub((int) $admin['idUtilisateur']),
        ]);

        $semaines = CalendrierDemo::courant()->semaines();
        Journaliseur::noter('reinitialisation', 'ParametreDemo', $semaines, "Jeu de démonstration recréé (décalage de $semaines semaine(s)).");

        return redirect()->to(site_url('admin/demo'))->with('succes', 'Le jeu de démonstration a été recréé avec des dates à jour.');
    }
}
