# Robotix19 — AP2 « Dynamisation » : document de design

- **Date :** 2026-10-05
- **Auteur :** Ihab Benmahrouz (BTS SIO — AP2)
- **Statut :** validé en discussion, en attente de relecture
- **Prérequis :** sous-projet 1 (grille AP1) livré — voir `2026-10-01-robotix19-design.md`
- **Suite prévue :** AP3 (« Club Robotix » : animateurs, inscriptions, réunions, T-SQL) dans un design séparé ; les tables de ce document sont conçues pour l'accueillir sans refonte.

## 1. Objectif

Couvrir les deux grilles de l'annexe 4 du sujet AP2 (pages 12 et 13), en adaptant le vocabulaire « adhérent » à Robotix : **l'adhérent est un client inscrit au Club Robotix**, il paie une adhésion annuelle (formule), il est classé dans une **catégorie d'âge**, et il peut **réserver des places** pour les événements du calendrier.

### Grille Phase 1 — Base de données et accès aux données (/30)

| Critère | Réalisation |
|---|---|
| MCD : personnes, tarifs, calendrier, formulaire (10) | Schéma `docs/mcd-robotix.md` (diagramme Mermaid + dictionnaire), tables ajoutées par migration, à reproduire dans Looping |
| Base (10) | SQL Server (Robotix58) — **écart assumé** avec « MySQL » : SQL Server est exigé par l'AP3 et déjà utilisé par Robotix |
| Jeu d'essai (10) | `RobotixSeeder` complété : 3 catégories, 3 formules, 12 clients répartis sur 2025 et 2026, adhésions, intérêts, réservations |
| Enregistrement du formulaire avec hashage, via PDO (10) | Inscription enregistrée par `ClubPdo` (requêtes préparées), `password_hash()` |
| Composants : liste déroulante ou boutons d'option, cases à cocher (10) | Formule en **boutons d'option** (lus en base), centres d'intérêt en **cases à cocher** (catégories de robots lues en base), date de naissance, photo |
| Profil global et par catégorie, liste déroulante alimentée par la base (10) | `/admin/clients` : liste de tous les adhérents, filtrée par une liste déroulante des catégories d'âge lue en base ; `/compte/profil` : profil de l'adhérent connecté |
| Statistiques sans et avec paramètres, par fonction (10) | `/admin/statistiques` : `montantTotalAdhesions()` (sans paramètre), `adhesionsParCategorie(int $annee)` et `nombreAdhesions(int $idCategorie, int $annee)` (avec paramètres) |
| CRUD : mise à jour et suppression d'un adhérent (10) | `/admin/clients/{id}/modifier` et suppression (avec confirmation) |

### Grille Phase 2 — Authentification et programmation objet (/30)

| Critère | Réalisation |
|---|---|
| Formulaire d'authentification, gestion des sessions (10) | Existant (`/compte/connexion`, filtres `auth`/`admin`, session régénérée) ; la déconnexion vide aussi le panier |
| Classe `Reservation` et son test (15) | `App\Libraries\Reservation` + `tests/unit/ReservationTest.php` |
| Tableau des réservations possibles, Panier (tableaux d'objets) | `PanierReservations::chargerReservationsPossibles()` et propriété `$panier` (tableau de `Reservation`, conservé en session) |
| Fonctions : chargement/listage, ajout, listage du panier, enregistrement en table | `/reservations`, `/reservations/ajouter`, `/reservations/panier`, `/reservations/enregistrer`, `/reservations/vider` ; table `Panier` |
| RGPD — cookies (5) | Bandeau maison (onglets Consentement / Détails / À propos ; boutons Refuser / Personnaliser / Tout autoriser), cookie `robotix_consentement`, Google Maps chargé seulement après accord |

### Hors périmètre

- Mise en production sur SRVSIOC (phase 9.4) : documentée dans la recette, non automatisée.
- Fichier Looping `.loo` : l'étudiant le produit à partir du schéma fourni.
- Achat de robots (panier e-commerce du Store) : reste au sous-projet « Store transactionnel ».

## 2. Données (migration `CreateClubRobotix`)

```sql
CategorieAge (idCategorieAge INT IDENTITY PK, libelle VARCHAR(30) NOT NULL UNIQUE,
              ageMin INT NOT NULL, ageMax INT NOT NULL, CHECK (ageMin <= ageMax))
Reduction    (idReduction INT IDENTITY PK, idCategorieAge INT NOT NULL UNIQUE FK,
              txReduction DECIMAL(5,2) NOT NULL CHECK (txReduction BETWEEN 0 AND 100))
Tarif        (idTarif INT IDENTITY PK, code VARCHAR(30) NOT NULL UNIQUE, libelle VARCHAR(80) NOT NULL,
              famille VARCHAR(10) NOT NULL CHECK (famille IN ('formule','option')),
              mode VARCHAR(12) NOT NULL CHECK (mode IN ('fixe','pourcentage')),
              valeur DECIMAL(10,2) NOT NULL CHECK (valeur >= 0), description VARCHAR(300) NULL,
              actif BIT NOT NULL DEFAULT 1)
Adhesion     (idAdhesion INT IDENTITY PK, idUtilisateur INT NOT NULL FK Client,
              annee INT NOT NULL, dateAdhesion DATETIME NOT NULL DEFAULT GETDATE(),
              idTarif INT NOT NULL FK, montant DECIMAL(10,2) NOT NULL CHECK (montant >= 0),
              UNIQUE (idUtilisateur, annee))
ClientInteret (idUtilisateur INT FK Client, idCategorie INT FK Categorie, PK (idUtilisateur, idCategorie))
Panier       (idPanier INT IDENTITY PK, idEvenement INT NOT NULL FK, idUtilisateur INT NOT NULL FK Client,
              nomEvenement VARCHAR(150) NOT NULL, dateResa DATETIME NOT NULL DEFAULT GETDATE(),
              nbPlace INT NOT NULL CHECK (nbPlace > 0))

ALTER TABLE Client    ADD dateNaissance DATE NULL, photo VARCHAR(255) NULL, idCategorieAge INT NULL FK
ALTER TABLE Evenement ADD nbPlaces INT NOT NULL DEFAULT 30 CHECK (nbPlaces >= 0)
```

Règles de gestion :

- **RG1** — La catégorie d'âge est calculée à l'inscription à partir de l'âge (en années révolues) : Jeune 18–24, Adulte 25–59, Senior 60–120. L'inscription est refusée avant 18 ans.
- **RG2** — Réductions : Jeune 15 %, Adulte 0 %, Senior 10 %.
- **RG3** — Formules d'adhésion annuelles (famille `formule`) : Découverte 49 €, Passion 99 €, Premium 199 €. Montant de l'adhésion = valeur de la formule × (1 − réduction de la catégorie), arrondi au centime.
- **RG4** — Une seule adhésion par client et par année ; la date d'adhésion est enregistrée.
- **RG5** — Les services (garantie, livraison, maintenance, prise en main) passent de la classe `Tarifs` à la table `Tarif` (famille `option`) ; la page Tarifs et le calculateur lisent la base. La classe `Tarifs` ne garde que les durées de financement.
- **RG6** — Places disponibles d'un événement = `nbPlaces` − somme des `Panier.nbPlace` de cet événement. Une réservation ne peut dépasser les places disponibles ; seuls les événements à venir sont réservables.
- **RG6 bis** — Seuls les comptes **clients** réservent (la table `Panier` référence `Client`) ; un administrateur ou un rédacteur voit la liste mais le bouton « Ajouter au panier » lui est refusé avec un message.
- **RG7** — Supprimer un adhérent supprime ses intérêts, adhésions, réservations et adresses puis le compte ; c'est refusé s'il a des commandes (on propose alors de le désactiver).

## 3. Accès aux données avec PDO

- `App\Libraries\RobotixPdo` : connexion PDO unique (`sqlsrv:Server=<hôte>,<port>;Database=<base>`), paramètres lus dans la configuration `Database` (groupe courant, donc la base de test pendant les tests), `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, encodage UTF-8. Méthodes : `lignes(string $sql, array $parametres = []): array`, `ligne(...)`: ?array, `valeur(...)`: mixed, `executer(...)`: int (lignes touchées), `dernierId(): int`, `transaction(callable $travail): mixed`.
- `App\Models\Pdo\ClubPdo` : inscription (utilisateur + client + adresse + intérêts + adhésion dans une transaction), profil, liste des adhérents (toutes catégories ou une), mise à jour, suppression, catégories, formules.
- `App\Models\Pdo\StatistiquesPdo` : `montantTotalAdhesions(): float` (année en cours), `adhesionsParCategorie(int $annee): array` (libellé, nombre, taux en %), `nombreAdhesions(int $idCategorieAge, int $annee): int`.
- Toutes les requêtes sont préparées (`:parametre`), jamais concaténées.

## 4. Pages

| Route | Accès | Contenu |
|---|---|---|
| `GET/POST /compte/inscription` | public | Formulaire enrichi : date de naissance, photo (jpg/png/webp, 2 Mo max, facultative), formule en boutons d'option avec prix réduit affiché en direct (JS), intérêts en cases à cocher |
| `GET /compte/profil` | connecté | Nom, prénom, photo, e-mail, date d'adhésion, catégorie, formule et montant payé, intérêts, réservations enregistrées |
| `POST /compte/formule` | connecté | **Ajax** (fetch, JSON) : changement de formule de l'année en cours, montant recalculé (sujet phase 6.4) |
| `GET /admin/clients` | admin | Tableau des adhérents ; liste déroulante des catégories (lue en base) qui filtre par `?categorie=` |
| `GET/POST /admin/clients/{id}/modifier` | admin | Mise à jour : nom, prénom, e-mail, téléphone, date de naissance (catégorie recalculée), actif |
| `POST /admin/clients/{id}/supprimer` | admin | Suppression (RG7), confirmation `data-confirm` |
| `GET /admin/statistiques` | admin | Montant total de l'année, tableau et barres CSS par catégorie, sélecteur d'année |
| `GET /reservations` | connecté | Événements à venir avec places disponibles, champ nombre de places + « Ajouter au panier » |
| `POST /reservations/ajouter` | connecté | Ajoute au panier (cumule si l'événement y est déjà) |
| `GET /reservations/panier` | connecté | Panier complet : retour à la liste, enregistrer, vider |
| `POST /reservations/enregistrer` | connecté | Panier → table `Panier` (transaction, places revérifiées), puis panier vidé |
| `POST /reservations/vider` | connecté | Annule le panier |

Le menu « Compte » ajoute « Mon profil » et « Réserver des places » ; le menu admin ajoute « Adhérents » et « Statistiques ». La photo est enregistrée dans `public/uploads/clients/` sous un nom aléatoire ; un avatar par défaut est affiché sinon.

## 5. Programmation objet : le panier de réservations

```
Reservation
- idEvenement : int        - nomEvenement : string
- dateResa : string        - nbPlaceDispo : int        - nbPlace : int
+ __construct(idEvenement, nomEvenement, dateResa, nbPlaceDispo, nbPlace = 0)
+ get…() / set…()          (setNbPlace refuse < 1 ou > nbPlaceDispo)
+ miseAJourNbPlaceDispo()  (nbPlaceDispo -= nbPlace)
+ versTableau() / depuisTableau()   (stockage en session)
```

`PanierReservations` (dépend de `RobotixPdo` et de la session) :
`chargerReservationsPossibles(): Reservation[]` (le **TableauDeReservationPossible**), `ajouter(int $idEvenement, int $nbPlace): Reservation`, `lister(): Reservation[]` (le **Panier**), `nombreDePlaces(): int`, `enregistrer(int $idUtilisateur): int`, `vider(): void`.

## 6. RGPD — consentement aux cookies

- Partial `partials/cookies.php` inclus dans le layout + `public/js/consentement.js`.
- Catégories : nécessaires (toujours actives : session, CSRF), préférences, statistiques, contenus tiers (Google Maps).
- Cookie `robotix_consentement` = JSON `{"v":1,"preferences":bool,"statistiques":bool,"tiers":bool,"date":"AAAA-MM-JJ"}`, durée 6 mois, `SameSite=Lax`.
- Le bandeau s'affiche tant que le cookie est absent ; « Gérer les cookies » dans le pied de page le rouvre.
- Google Maps : l'`iframe` porte `data-src` ; elle n'est chargée que si `tiers` est accepté, sinon un encart propose « Autoriser Google Maps ».
- Fonctions pures testées avec Node : `lire(chaineCookie)`, `serialiser(choix, date)`, `toutAccepter()`, `toutRefuser()`.

## 7. Erreurs et sécurité

- Validation serveur CodeIgniter sur tous les formulaires, double contrôle HTML5 + `validation.js`.
- Envoi de photo : type MIME vérifié, taille ≤ 2 Mo, nom aléatoire, extension imposée.
- Réservation : quantité entière ≥ 1, ≤ places disponibles (revérifiée à l'enregistrement dans la transaction).
- `esc()` dans toutes les vues ; CSRF sur tous les POST (l'appel Ajax envoie l'en-tête `X-CSRF-TOKEN`).

## 8. Tests

- PHPUnit : `ReservationTest` (unitaire), `PanierReservationsTest`, `ClubPdoTest`, `StatistiquesPdoTest`, tests de fonctionnalités pour inscription, profil, admin clients, statistiques, réservations et formule Ajax.
- Isolation : PDO ouvre aussi une transaction annulée à la fin de chaque test ; `SET LOCK_TIMEOUT 5000` sur les deux connexions pour échouer vite plutôt que bloquer. Les assertions sur des données écrites par PDO sont lues par PDO.
- Node : `consentement.test.js`, et le calcul du prix réduit de l'inscription.
- Recette : W3C (0 erreur) sur les nouvelles pages, captures responsive, `docs/recette-ap2.md` reprenant les deux grilles.

## 9. Découpage

1. Migration, seeder, MCD, `RobotixPdo`.
2. Inscription enrichie, profil, formule Ajax.
3. Admin adhérents (liste par catégorie, CRUD) et statistiques.
4. Classe `Reservation`, `PanierReservations`, pages de réservation.
5. Bandeau RGPD, Google Maps conditionnel.
6. Recette AP2 (W3C, captures, fiche de recette, portfolio).
