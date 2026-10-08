<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Réserve une page aux administrateurs. */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session('idUtilisateur')) {
            session()->set('redirection', current_url());

            return redirect()->to(site_url('compte/connexion'))->with('erreur', 'Connectez-vous pour accéder à cette page.');
        }

        if (session('role') !== 'admin') {
            return redirect()->to(site_url('/'))->with('erreur', 'Accès réservé aux administrateurs.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
