# Robotix19 — Document de design

- **Date :** 2026-10-01
- **Auteur :** Ihab Benmahrouz (BTS SIO1 — AP1)
- **Statut :** validé en discussion, en attente de relecture

## 1. Objectif

Robotix19 est un site web de démonstration pour une enseigne fictive, **Robotix**, qui vend des robots humanoïdes aux particuliers. Il comporte deux univers :

- **Robotix Store** : catalogue, fiche robot, panier, commande avec paiement simulé, compte client, back-office admin.
- **Robotix News** : articles qui parlent des robots vendus sur le Store (sous-projet 2, livré après le Store).

Le projet est évalué avec la **grille AP1 (/30)**. Chaque critère doit être démontrable dans le navigateur. Les fonctionnalités Store et News au-delà de la grille sont des « suppléments à bonifier ».

### Critères de réussite

1. Chaque ligne de la grille AP1 correspond à une page ou une fonctionnalité visible (voir §3).
2. Le site est responsive (375 px, 768 px, 1280 px) sans défilement horizontal.
3. Toutes les pages publiques passent le validateur W3C (HTML) sans erreur.
4. Un client peut s'inscrire, remplir son panier, commander et voir sa commande dans son historique.
5. Un administrateur peut gérer produits, marques, catégories, images, événements, showrooms et statuts de commande.

### Hors périmètre

- Paiement réel (Stripe, PayPal…) : le paiement est **simulé**.
- Envoi réel d'e-mails : le formulaire de contact enregistre le message et affiche une confirmation.
- Mise en production sur un hébergeur.

## 2. Contexte technique

| Élément | Valeur |
|---|---|
| Framework | CodeIgniter 4.6.5 (déjà installé dans `C:\laragon\www\Robotix19`) |
| PHP | 8.1.10 (Laragon, extensions `sqlsrv` / `pdo_sqlsrv`) |
| Base | SQL Server, base **Robotix58**, serveur `WIN-CIL97SHCG0U:1433`, pilote `SQLSRV` |
| Serveur web | Apache (Laragon), URL `http://localhost/Robotix19/` (le `.htaccess` racine redirige vers `public/`) |
| Front | Bootstrap 5 (CDN) + `public/css/robotix.css` + JavaScript vanilla |
| Polices | Orbitron (titres) et Inter (texte), via Google Fonts |

### Règles SQL Server à respecter (issues de BookClub18)

- Dates : `->set('col', 'GETDATE()', false)`. Jamais `date('Y-m-d H:i:s')` ni `new \DateTime()` passés au QueryBuilder.
- `TOP N` au lieu de `LIMIT N` en SQL brut ; pas de `AS` devant l'alias d'une table dérivée.
- Toutes les colonnes non agrégées dans le `GROUP BY` ; `CAST(x AS FLOAT)` pour `AVG` sur des entiers.
- `$this->db` uniquement dans les modèles ; `db_connect()` dans les contrôleurs ; `service('request')` dans les vues.

### Configuration

- Dans `.env`, `app.baseURL = 'http://localhost/Robotix19/'`, pour que les liens générés n'incluent pas `/public/`.
- Dans `Config/App.php`, `$indexPage = ''` (URL sans `index.php`).

## 3. Correspondance avec la grille AP1

| Critère (pts) | Réalisation |
|---|---|
| Structuration des dossiers dans `www` (1) | `www/Robotix19/` en MVC : `app/Controllers`, `app/Models`, `app/Views`, `public/css`, `public/js`, `public/images`. |
| Test gabarit responsive (1) | Gabarit maison découpé en `Views/layouts/main.php` + `Views/partials/{header,nav,footer}.php`, testé dans DevTools à 375, 768 et 1280 px. |
| CSS personnel (2) | `public/css/robotix.css` : variables de couleurs, typographies, animations, surcharge de Bootstrap. |
| Menus et navigation (2) | Menu Accueil · Store · News · Tarifs · Événements · Showrooms · Contact · Compte ; burger sur mobile ; lien actif mis en évidence ; menu Compte selon le rôle. |
| Présentation de l'activité (2) | Page d'accueil : bandeau d'accroche, les trois univers (Store, News, Services), robots phares, prochains événements, derniers articles. |
| GoogleMap affichage (1) | Page `/showrooms` : carte Google Maps intégrée par `iframe` (sans clé API). |
| GoogleMap gestion (1) | Liste des showrooms chargée depuis `/api/showrooms` ; un clic recentre la carte (changement du `src` de l'iframe) et affiche adresse, téléphone et horaires ; boutons zoom + / − ; lien « Itinéraire » (Google Maps directions) ; bouton « Showroom le plus proche » (géolocalisation du navigateur + calcul de distance en JS). |
| Images réactives (3) | **Image réactive à zones cliquables** (`<map>` / `<area>`) sur l'accueil et la fiche robot : un robot humanoïde dont la tête, les mains, le torse et les jambes sont des zones cliquables, mises en évidence au survol (`image-map.js` recalcule les coordonnées quand l'image est redimensionnée). En complément : images responsives (`srcset`, `img-fluid`) et zoom ou seconde image au survol des cartes du catalogue. |
| Popup / div / page (2) | Clic sur une zone de l'image réactive → **popup** (modale) décrivant la fonction (caméras, préhension, batterie, motricité) ; survol → infobulle dans une **div** ; lien « Voir la fiche » → **page** robot. Fiche robot : vignettes qui changent l'image principale (div) et lightbox (popup) avec précédent/suivant et Échap. |
| Calendrier des événements (3) | Page `/evenements` : calendrier mensuel codé à la main (`calendrier.js`), mois précédent/suivant, bouton « Aujourd'hui », pastilles colorées par type, filtre par type et par showroom, panneau de détail au clic sur un jour, données lues sur `/api/evenements?mois=AAAA-MM`. **Gestion du planning** dans l'admin (`/admin/evenements`) : ajout, modification, suppression, contrôle que `dateFin > dateDebut` et pas de chevauchement dans un même showroom. |
| Présentation des tarifs (2) | Page `/tarifs` : trois gammes (Compagnon, Domestique, Premium) et services (garantie étendue, livraison et installation, contrat de maintenance). |
| Calculateur JavaScript (4) | Configurateur sur `/tarifs` (`calculateur.js`) : choix du robot (prix lus en base, injectés en JSON), options cochables, quantité, financement 1/12/24/36 mois ; affichage en direct du total HT, de la TVA (20 %), du TTC et de la mensualité ; bouton « Ajouter au panier ». |
| Champs adaptés à l'activité (2) | `/compte/inscription` (nom, prénom, e-mail, téléphone, adresse, code postal, ville, mot de passe, confirmation) et `/contact` (nom, e-mail, téléphone, objet : démo / devis / SAV / autre, robot concerné, message, consentement RGPD). |
| Contrôle des champs (2) | Attributs HTML5 (`required`, `type`, `pattern`, `minlength`) + `validation.js` (regex : e-mail, téléphone FR, code postal 5 chiffres, mot de passe ≥ 8 caractères avec majuscule, minuscule et chiffre) avec messages en direct et blocage de l'envoi ; puis double contrôle serveur par les règles de validation CI4. |
| W3C + PortFolio (2) | Toutes les pages publiques validées sur validator.w3.org ; une carte « Robotix19 » ajoutée à `AP0 AP1/Le Portfolio - 1/projets.html`, avec lien et captures. |

## 4. Architecture

### Arborescence

```
app/
├── Config/Routes.php, Filters.php
├── Controllers/
│   ├── Pages.php            accueil, tarifs, évènements, showrooms
│   ├── Contact.php
│   ├── Api.php              JSON : evenements, showrooms
│   ├── Store/   Catalogue.php, Produit.php, Panier.php, Commande.php
│   ├── Compte/  Auth.php (connexion, inscription, déconnexion), Profil.php
│   └── Admin/   Dashboard.php, Produits.php, Marques.php, Categories.php,
│                Commandes.php, Evenements.php, Showrooms.php
├── Filters/     AuthFilter.php (connecté), AdminFilter.php (administrateur)
├── Libraries/   Panier.php (panier en session), Journaliseur.php (table Journal)
├── Models/      UtilisateurModel, ClientModel, AdministrateurModel, AdresseModel,
│                ProduitModel, ImageModel, MarqueModel, CategorieModel,
│                CommandeModel, ContenirModel, PaiementModel, HistoriqueStatutModel,
│                JournalModel, EvenementModel, ShowroomModel, MessageContactModel
├── Views/
│   ├── layouts/   main.php, admin.php
│   ├── partials/  header.php, nav.php, footer.php, flash.php, carte_robot.php
│   ├── pages/     accueil, tarifs, evenements, showrooms, contact
│   ├── store/     catalogue, produit, panier, commande_adresses, commande_paiement, commande_confirmation
│   ├── compte/    connexion, inscription, profil, commandes, commande_detail
│   └── admin/     dashboard + listes / formulaires par entité
└── Database/
    ├── Migrations/  CreateShowroomEvenementContact
    └── Seeds/       RobotixSeeder
public/
├── css/robotix.css, admin.css
├── js/  main.js, calculateur.js, calendrier.js, carte.js, galerie.js, image-map.js, validation.js
└── images/ robots/, marques/, showrooms/, ui/
```

### Rôles et authentification

- Connexion par e-mail et mot de passe : `password_hash()` / `password_verify()` sur `Utilisateur.motDePasse`. Le compte doit avoir `actif = 1`.
- Le rôle est déduit de la table spécialisée : présence dans `Administrateur` → `admin`, dans `Redacteur` → `redacteur`, sinon dans `Client` → `client`.
- La session contient `idUtilisateur`, `nom`, `prenom` et `role`. La session est régénérée à la connexion.
- `AuthFilter` protège `/compte/*` (sauf connexion et inscription), `/panier/valider` et `/commande/*`. `AdminFilter` protège `/admin/*`.
- CSRF CI4 activé sur tous les formulaires POST ; échappement `esc()` dans toutes les vues.

### Routes

| Méthode | Route | Contrôleur |
|---|---|---|
| GET | `/` | `Pages::accueil` |
| GET | `/tarifs` | `Pages::tarifs` |
| GET | `/evenements` | `Pages::evenements` |
| GET | `/showrooms` | `Pages::showrooms` |
| GET/POST | `/contact` | `Contact::index` / `Contact::envoyer` |
| GET | `/api/evenements`, `/api/showrooms` | `Api::evenements`, `Api::showrooms` |
| GET | `/store`, `/store/robot/(:num)` | `Store\Catalogue::index`, `Store\Produit::show` |
| GET/POST | `/panier`, `/panier/ajouter`, `/panier/modifier`, `/panier/retirer` | `Store\Panier` |
| GET/POST | `/commande/adresses`, `/commande/paiement`, `/commande/confirmation/(:num)` | `Store\Commande` (filtre auth) |
| GET/POST | `/compte/connexion`, `/compte/inscription`, `/compte/deconnexion` | `Compte\Auth` |
| GET | `/compte`, `/compte/commandes`, `/compte/commandes/(:num)` | `Compte\Profil` (filtre auth) |
| GET/POST | `/admin/...` | `Admin\*` (filtre admin) |
| GET | `/news`, `/news/(:segment)` | sous-projet 2 |

## 5. Données

### Tables existantes utilisées (Robotix58)

`Utilisateur`, `Client`, `Administrateur`, `Redacteur`, `Adresse`, `Produit`, `Categorie`, `Marque`, `Image`, `CompatibiliteProduit`, `Commande`, `Contenir`, `Paiement`, `HistoriqueStatut`, `Journal`. Tables News (sous-projet 2) : `Article`, `Thematique`, `Aborder`, `Mentionner`, `Suivre`.

### Tables ajoutées (migration CI4)

```sql
Showroom (
  idShowroom   INT IDENTITY PRIMARY KEY,
  nom          VARCHAR(100) NOT NULL,
  adresse      VARCHAR(200) NOT NULL,
  codePostal   VARCHAR(5)   NOT NULL,
  ville        VARCHAR(100) NOT NULL,
  latitude     DECIMAL(9,6) NOT NULL,
  longitude    DECIMAL(9,6) NOT NULL,
  telephone    VARCHAR(20)  NULL,
  horaires     VARCHAR(200) NULL,
  image        VARCHAR(255) NULL
)

Evenement (
  idEvenement  INT IDENTITY PRIMARY KEY,
  titre        VARCHAR(150) NOT NULL,
  description  VARCHAR(1000) NULL,
  type         VARCHAR(20)  NOT NULL,   -- 'demo' | 'lancement' | 'salon' | 'atelier'
  dateDebut    DATETIME     NOT NULL,
  dateFin      DATETIME     NOT NULL,
  idShowroom   INT NULL REFERENCES Showroom(idShowroom),
  idProduit    INT NULL REFERENCES Produit(idProduit)
)

MessageContact (
  idMessage    INT IDENTITY PRIMARY KEY,
  nom          VARCHAR(100) NOT NULL,
  email        VARCHAR(150) NOT NULL,
  telephone    VARCHAR(20)  NULL,
  objet        VARCHAR(20)  NOT NULL,   -- 'demo' | 'devis' | 'sav' | 'autre'
  idProduit    INT NULL REFERENCES Produit(idProduit),
  message      VARCHAR(2000) NOT NULL,
  dateEnvoi    DATETIME     NOT NULL DEFAULT GETDATE(),
  traite       BIT          NOT NULL DEFAULT 0
)
```

`MessageContact` est ajoutée pour que le formulaire de contact enregistre réellement les messages (consultables dans l'admin), puisqu'aucun e-mail n'est envoyé.

### Données de démonstration (`RobotixSeeder`)

- 5 marques (Unitree, Figure AI, Agility Robotics, 1X, Pollen Robotics) ; 4 catégories (Compagnon, Domestique, Éducatif, Premium).
- 10 robots avec référence, prix HT, TVA à 20 %, stock, 2 à 4 images chacun, quelques compatibilités.
- 3 showrooms (Paris, Lyon, Marseille) avec coordonnées réelles de quartiers commerciaux.
- 12 événements répartis d'octobre à décembre 2026.
- Comptes de test : `client@robotix.test`, `redacteur@robotix.test` et `admin@robotix.test`, mot de passe `Robotix2026!`.
- Images : visuels libres de droits ou illustrations générées, stockés dans `public/images/robots/`.

## 6. Parcours de commande

1. **Panier** (session, `Libraries/Panier.php`) : ajout, modification de quantité (plafonnée au stock), retrait, totaux HT, TVA et TTC.
2. **Adresses** (connexion requise) : choix ou création d'une adresse de livraison et d'une adresse de facturation (table `Adresse`).
3. **Paiement simulé** : formulaire carte factice (numéro de 16 chiffres validé par l'algorithme de Luhn, date d'expiration future, CVC de 3 chiffres). Aucune donnée de carte n'est stockée.
4. **Validation**, dans une **transaction SQL** :
   - vérification du stock de chaque ligne (sinon annulation et message) ;
   - `INSERT Commande` (`statutCourant = 'payee'`, `dateCommande = GETDATE()`) ;
   - `INSERT Contenir` par ligne, avec le prix et la TVA figés au moment de l'achat ;
   - `INSERT Paiement` (`mode = 'carte'`, `statutPaiement = 'valide'`, référence `RBX-AAAAMMJJ-XXXXXX`) ;
   - `INSERT HistoriqueStatut` (`null` → `payee`) ;
   - `UPDATE Produit SET stock = stock - quantite` ;
   - `INSERT Journal`.
5. **Confirmation** : récapitulatif et numéro de commande ; le panier est vidé.

Cycle des statuts (géré par l'admin, chaque changement est tracé dans `HistoriqueStatut`) :
`payee → en_preparation → expediee → livree`, avec `annulee` possible avant `expediee` (le stock est alors réintégré).

## 7. Gestion des erreurs

- Validation serveur CI4 sur chaque formulaire ; en cas d'erreur, retour au formulaire avec les anciennes valeurs (`old()`) et les messages par champ.
- Messages flash (succès, erreur) affichés par `partials/flash.php`.
- Robot introuvable ou inactif → page 404 CI4.
- Erreur dans la transaction de commande → `transRollback()`, message à l'utilisateur, panier conservé.
- API JSON : paramètre `mois` invalide → réponse HTTP 400 avec un message.
- Côté JavaScript : si l'API ne répond pas, le calendrier et la carte affichent un message au lieu de rester vides.

## 8. Style visuel

- Thème « tech futuriste » : fond clair avec des sections sombres (`#0b1020`), accent cyan électrique (`#00d4ff`) et une seconde couleur violette (`#7c3aed`).
- Titres en Orbitron, texte en Inter.
- Cartes robots avec ombre et zoom au survol ; boutons avec un léger effet lumineux.
- Mobile d'abord, points de rupture Bootstrap (`sm`, `md`, `lg`) ; menu burger sous `lg`.
- Contraste suffisant (WCAG AA) et attributs `alt` sur toutes les images.

## 9. Tests et validation

- **Responsive :** chaque page vérifiée dans DevTools à 375, 768 et 1280 px.
- **W3C :** chaque page publique soumise à validator.w3.org (HTML) et jigsaw.w3.org (CSS pour `robotix.css`) ; zéro erreur.
- **Formulaires :** pour l'inscription, le contact et le paiement, un jeu de valeurs valides et un jeu de valeurs invalides par champ (côté JS puis côté serveur, JS désactivé).
- **Calculateur :** vérification manuelle de 3 configurations contre un calcul fait à la main.
- **Commande :** commande réussie, stock insuffisant (annulation complète), accès à `/commande` sans connexion (redirection), accès à `/admin` par un client (refus).
- **Base :** `php spark migrate` puis `php spark db:seed RobotixSeeder` sur une base vide.

## 10. Découpage

1. **Sous-projet 1 — Socle et grille AP1 :** gabarit, accueil, image réactive à zones, Store en consultation (catalogue et fiche), tarifs et calculateur, événements avec gestion du planning (admin des événements, donc connexion et rôle admin inclus), showrooms et Google Maps, contact, inscription et connexion, migration et seeder, W3C, portfolio.
2. **Sous-projet 1 bis — Store transactionnel (bonus) :** panier, commande, paiement simulé, compte client, reste du back-office admin (produits, marques, catégories, commandes, showrooms, messages).
3. **Sous-projet 2 — Robotix News (bonus) :** articles, thématiques, liens vers les robots, espace rédacteur.

Chaque sous-projet a son plan d'implémentation. Ce document couvre en détail les sous-projets 1 et 1 bis ; Robotix News fera l'objet d'un design dédié.
