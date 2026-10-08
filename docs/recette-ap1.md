# Recette AP1 — Robotix19

| Critère (pts) | Où le montrer | Manipulation | Résultat |
|---|---|---|---|
| Structuration des dossiers (1) | Explorateur : `www/Robotix19` | Montrer `app/Controllers`, `app/Models`, `app/Views`, `public/css`, `public/js`, `public/images` | ✅ |
| Gabarit responsive (1) | Toutes les pages | DevTools 375 / 768 / 1280 px (captures dans `docs/captures/`) | ✅ |
| CSS personnel (2) | `public/css/robotix.css` | Variables `--rbx-*`, animations, surcharge Bootstrap | ✅ |
| Menus et navigation (2) | Barre de navigation | Rubrique active soulignée, menu burger sur mobile | ✅ |
| Présentation de l'activité (2) | `/` | Bandeau, « Notre activité », robots phares | ✅ |
| GoogleMap affichage (1) | `/showrooms` | Carte du showroom de Lyon | ✅ |
| GoogleMap gestion (1) | `/showrooms` | Changement de showroom, zoom, itinéraire, showroom le plus proche | ✅ |
| Images réactives (3) | `/` et fiche robot | Survol des zones du robot (`<map>`/`<area>`), cartes du catalogue | ✅ |
| Popup / div / page (2) | `/` et fiche robot | Popup d'une zone, aperçu dans une div, lightbox de la galerie, lien vers la fiche | ✅ |
| Gestion de planning (3) | `/evenements` et `/admin/evenements` | Navigation par mois, filtres, détail du jour ; ajout, modification et suppression avec contrôle des chevauchements | ✅ |
| Présentation des tarifs (2) | `/tarifs` | Gammes « à partir de », tableau des services | ✅ |
| Calculateur JavaScript (4) | `/tarifs` | G1 + garantie + livraison = 19 029,60 € TTC ; quantité ; financement | ✅ |
| Champs adaptés (2) | `/contact`, `/compte/inscription` | Objet de la demande, robot concerné, adresse… | ✅ |
| Contrôle des champs (2) | `/contact`, `/compte/inscription` | Attributs `pattern` + `validation.js` (regex) + contrôle serveur | ✅ |
| W3C + PortFolio (2) | `tools/w3c.ps1`, portfolio | 0 erreur HTML ; carte Robotix dans `projets.html` | ✅ |

## Preuves

- **Tests automatiques** : `php vendor/bin/phpunit --no-coverage` → 78 tests verts ; `node --test "tests/js/*.test.js"` → 29 tests verts.
- **W3C HTML** : `powershell -ExecutionPolicy Bypass -File tools\w3c.ps1` → 0 erreur sur 10 pages (accueil, Store, fiche robot, News, Tarifs, Événements, Showrooms, Contact, Connexion, Inscription).
- **W3C CSS** : `robotix.css` valide (profil CSS3 + SVG), 0 erreur.
- **Responsive** : captures 375 / 768 / 1280 px dans `docs/captures/`.
- **Portfolio** : carte « Robotix » dans `AP0 AP1/Le Portfolio - 1/projets.html` (capture `docs/captures/portfolio-carte.png`).

## Comptes de démonstration

Mot de passe commun : `Robotix2026!`

| Rôle | E-mail | À montrer |
|---|---|---|
| Administrateur | `admin@robotix.test` | Menu Compte → Gérer le planning |
| Client | `client@robotix.test` | Connexion, prénom dans le menu |

## Lancer le site

- Avec Laragon (Apache) : http://localhost/Robotix19/
- En ligne de commande : `set app_baseURL=http://localhost:8080/` puis `php spark serve` → http://localhost:8080
- Remettre les données de démonstration : `php spark db:seed RobotixSeeder`
