<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Pages vitrine
$routes->get('/', 'Pages::accueil');
$routes->get('news', 'Pages::news');
$routes->get('credits', 'Pages::credits');
$routes->get('a-propos', 'Pages::aPropos');
$routes->get('tarifs', 'Pages::tarifs');
$routes->get('evenements', 'Pages::evenements');
$routes->get('showrooms', 'Pages::showrooms');
$routes->get('contact', 'Contact::index');
$routes->post('contact', 'Contact::envoyer');

// Robotix Store
$routes->get('store', 'Store\Catalogue::index');
$routes->get('store/robot/(:num)', 'Store\Produit::show/$1');

// API JSON (calendrier et carte) — exclue du filtre CSRF
$routes->get('api/evenements', 'Api::evenements');
$routes->get('api/showrooms', 'Api::showrooms');

// Compte
$routes->group('compte', static function ($routes) {
    $routes->get('profil', 'Compte\Profil::index', ['filter' => 'auth']);
    $routes->post('formule', 'Compte\Profil::formule', ['filter' => 'auth']);
    $routes->get('connexion', 'Compte\Auth::connexion');
    $routes->post('connexion', 'Compte\Auth::seConnecter');
    $routes->get('inscription', 'Compte\Auth::inscription');
    $routes->post('inscription', 'Compte\Auth::inscrire');
    $routes->post('deconnexion', 'Compte\Auth::deconnexion');
    // Mot de passe oublié : demande du lien, puis nouveau mot de passe
    $routes->get('mot-de-passe-oublie', 'Compte\MotDePasse::oublie');
    $routes->post('mot-de-passe-oublie', 'Compte\MotDePasse::demander');
    $routes->get('mot-de-passe/(:segment)', 'Compte\MotDePasse::formulaire/$1');
    $routes->post('mot-de-passe/(:segment)', 'Compte\MotDePasse::changer/$1');
});

// Administration (réservée au rôle admin)
$routes->group('admin', ['filter' => 'admin'], static function ($routes) {
    // AP3 — organisation du club : animateurs et remplacement, rapports SQL Server
    $routes->get('club/animateurs', 'Admin\Animateurs::index');
    $routes->post('club/animateurs', 'Admin\Animateurs::creer');
    $routes->get('club/animateurs/(:num)/modifier', 'Admin\Animateurs::modifier/$1');
    $routes->post('club/animateurs/(:num)', 'Admin\Animateurs::mettreAJour/$1');
    $routes->post('club/animateurs/(:num)/supprimer', 'Admin\Animateurs::supprimer/$1');
    $routes->get('club/rapports', 'Admin\Rapports::index');
    $routes->post('evenements/(:num)/remplacer', 'Admin\Evenements::remplacer/$1');
    $routes->get('statistiques', 'Admin\Statistiques::index');
    // Jeu de démonstration : état et remise à zéro (seeder, dates à jour)
    $routes->get('demo', 'Admin\Demo::index');
    $routes->get('journal', 'Admin\Journal::index');
    $routes->post('demo/reinitialiser', 'Admin\Demo::reinitialiser');
    $routes->get('clients', 'Admin\Clients::index');
    $routes->get('clients/(:num)/modifier', 'Admin\Clients::modifier/$1');
    $routes->post('clients/(:num)', 'Admin\Clients::mettreAJour/$1');
    $routes->post('clients/(:num)/supprimer', 'Admin\Clients::supprimer/$1');
    $routes->get('/', static fn () => redirect()->to(site_url('admin/evenements')));
    $routes->get('evenements', 'Admin\Evenements::index');
    $routes->get('evenements/nouveau', 'Admin\Evenements::nouveau');
    $routes->post('evenements', 'Admin\Evenements::creer');
    $routes->get('evenements/(:num)/modifier', 'Admin\Evenements::modifier/$1');
    $routes->post('evenements/(:num)', 'Admin\Evenements::mettreAJour/$1');
    $routes->post('evenements/(:num)/supprimer', 'Admin\Evenements::supprimer/$1');
});

// Réservation de places (panier virtuel AP2)
$routes->group('reservations', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Reservations::index');
    $routes->post('ajouter', 'Reservations::ajouter');
    $routes->get('panier', 'Reservations::panier');
    $routes->post('enregistrer', 'Reservations::enregistrer');
    $routes->post('vider', 'Reservations::vider');
});

// Club Robotix (AP3) : page invité, espace membre (joueur), espace animateur (entraîneur)
$routes->get('club', 'Club::index');
$routes->group('club', static function ($routes) {
    $routes->get('membres', 'Club::membres', ['filter' => 'club:membre']);
    $routes->post('inscription', 'Club::inscrire', ['filter' => 'club:membre']);
    $routes->get('animateur', 'Club::animateur', ['filter' => 'club:animateur']);
    $routes->post('animateur/evenements/(:num)/presences', 'Club::pointer/$1', ['filter' => 'club:animateur']);
});
