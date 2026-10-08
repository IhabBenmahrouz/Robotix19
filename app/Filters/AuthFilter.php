<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Réserve une page aux utilisateurs connectés. */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session('idUtilisateur')) {
            session()->set('redirection', current_url());

            return redirect()->to(site_url('compte/connexion'))->with('erreur', 'Connectez-vous pour accéder à cette page.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
