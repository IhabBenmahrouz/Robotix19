# Recette AP2 — Robotix19 (Club Robotix)

## Phase 1 — Base de données et accès aux données (/30)

| Critère (pts) | Où le montrer | Manipulation | Résultat |
|---|---|---|---|
| MCD : personnes, tarifs, calendrier, formulaire (10) | `docs/mcd-robotix.md`, Looping | Montrer Utilisateur/Client, CategorieAge/Reduction/Tarif/Adhesion, Evenement/Panier, MessageContact | ✅ |
| Base de données (10) | SQL Server, base Robotix58 | Migrations `php spark migrate:status` ; écart assumé : SQL Server (imposé par l'AP3) au lieu de MySQL | ✅ |
| Jeu d'essai (10) | `php spark db:seed RobotixSeeder` | 12 adhérents, 17 adhésions, 2 réservations | ✅ |
| Enregistrement du formulaire avec hashage, via PDO (10) | `/compte/inscription`, `ClubPdo::inscrire()` | Inscription, puis `motDePasse` haché en base | ✅ |
| Composants du formulaire (10) | `/compte/inscription` | Formule en boutons d'option (lus en base), intérêts en cases à cocher, prix réduit en direct | ✅ |
| Profil global et par catégorie (10) | `/admin/clients`, `/compte/profil` | Liste déroulante des catégories alimentée par la base | ✅ |
| Statistiques sans et avec paramètres (10) | `/admin/statistiques`, `StatistiquesPdo` | `montantTotalAdhesions()` ; `montantAdhesions(2025)` ; `adhesionsParCategorie(2026)` | ✅ |
| CRUD : mise à jour et suppression (10) | `/admin/clients` | Modifier la date de naissance (catégorie recalculée), supprimer Paul Laurent | ✅ |

## Phase 2 — Authentification et programmation objet (/30)

| Critère (pts) | Où le montrer | Manipulation | Résultat |
|---|---|---|---|
| Formulaire d'authentification (5) | `/compte/connexion` | Connexion client puis admin | ✅ |
| Gestion des sessions (5) | Menu Compte, filtres `auth`/`admin` | Accès refusé sans connexion ; la déconnexion vide le panier | ✅ |
| Classe Reservation et son test | `app/Libraries/Reservation.php`, `tests/unit/ReservationTest.php` | `php vendor/bin/phpunit tests/unit/ReservationTest.php` | ✅ |
| Tableau des réservations possibles / Panier | `PanierReservations` | `chargerReservationsPossibles()`, propriété `$panier` | ✅ |
| Chargement / listage, ajout, listage du panier, enregistrement en table (15) | `/reservations`, `/reservations/panier` | 2 places sur un atelier, 1 sur une démo, enregistrer → table `Panier` | ✅ |
| RGPD — cookies (5) | Bandeau en bas de page | Onglets Consentement / Détails / À propos ; Refuser / Personnaliser / Tout autoriser ; Google Maps bloqué tant que refusé | ✅ |

## Sécurité de l'authentification (ajouts)

| Protection | Où le montrer | Manipulation | Résultat |
|---|---|---|---|
| Limite des tentatives | `/compte/connexion`, `LimiteurConnexion`, table `TentativeConnexion` | 5 mauvais mots de passe pour `client@robotix.test` → blocage 15 min, même avec le bon mot de passe ; 20 échecs depuis une même IP → IP bloquée | ✅ |
| Mot de passe oublié | Lien sur `/compte/connexion` → `/compte/mot-de-passe-oublie` | Demande pour `client@robotix.test` → mail dans **Rapports SQL Server** (table `MailAEnvoyer`) → ouvrir le lien → nouveau mot de passe ; le lien ne marche qu'une fois et expire au bout d'une heure ; seule l'empreinte SHA-256 du jeton est en base (`JetonMotDePasse`) | ✅ |
| Journal des actions | Compte → Journal des actions (`/admin/journal`) | Modifier un événement → ligne « modification » ; filtres par action et par table, pagination | ✅ |
| En-têtes et envois | Filtre `secureheaders` (`app/Config/Filters.php`), `public/uploads/.htaccess` | DevTools → Réseau : `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` ; photos : type, extension, poids et dimensions contrôlés, scripts interdits dans `uploads/` | ✅ |

## Preuves

- Tests : `php vendor/bin/phpunit --no-coverage` et `node --test "tests/js/*.test.js"`.
- W3C : `powershell -ExecutionPolicy Bypass -File tools\w3c.ps1` → 0 erreur (pages publiques et pages connectées).
- Captures : `docs/captures/ap2-*.png`.

## Comptes de démonstration (mot de passe `Robotix2026!`)

| Rôle | E-mail |
|---|---|
| Administrateur | `admin@robotix.test` |
| Adhérente (Adulte, Passion) | `client@robotix.test` |
| Adhérente (Jeune) | `emma.petit@exemple.fr` |
| Adhérent (Senior) | `louis.durand@exemple.fr` |
