<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Réserve une page à un profil du Club Robotix : « club:membre » ou « club:animateur ».
 */
class ClubFilter implements FilterInterface
{
    private const MESSAGES = [
        'membre'    => 'Cet espace est réservé aux membres du Club Robotix.',
        'animateur' => 'Cet espace est réservé aux animateurs du Club Robotix.',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session('idUtilisateur')) {
            session()->set('redirection', current_url());

            return redirect()->to(site_url('compte/connexion'))->with('erreur', 'Connectez-vous pour accéder à cette page.');
        }

        $profil = $arguments[0] ?? 'membre';
        if (! in_array($profil, (array) session('profilClub'), true)) {
            return redirect()->to(site_url('club'))->with('erreur', self::MESSAGES[$profil] ?? 'Accès refusé.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
