# Recette AP3 — Robotix19 (Club Robotix)

Comptes de démonstration (mot de passe commun `Robotix2026!`) :

| Rôle | Connexion (e-mail ou pseudo) |
|---|---|
| Organisation (administrateur) | `admin@robotix.test` |
| Entraîneur (animateur) | `karim.h` — Karim Haddad, remplacé par Inès (`ines.f`) |
| Joueur (membre) | `camille` — Camille Martin |
| Client hors club | `jules.moreau@exemple.fr` |

> **Dates du jeu d'essai.** Les dates citées ci-dessous sont celles d'un seeding fait la semaine du 5 octobre 2026.
> Le seeder décale toutes les dates d'un nombre entier de semaines pour qu'elles restent à venir (`App\Libraries\CalendrierDemo`, décalage enregistré dans la table `ParametreDemo`).
> Avant une présentation : **Compte → Jeu de démonstration → Réinitialiser la démo**. Ensuite, la page publique `/a-propos` affiche les dates à jour du parcours (réunion, période des événements suivis…).

## Programmation de base de données SQL Server (/60)

### Analyse des données (/20)

| Critère (pts) | Où le montrer | Résultat |
|---|---|---|
| Gestion des adhérents, héritage (5) | `docs/diagramme-ap3.md` ; tables `Utilisateur → Client → Membre / Animateur` | ✅ |
| Événement, Type, Animateur, Lieu (2) | `Evenement`, `TypeEvenement` (clé étrangère `type`), `Animateur`, `Showroom` | ✅ |
| Animateur remplaçant, réflexivité (2) | `Animateur.idRemplacant → Animateur` ; contrainte « pas soi-même » | ✅ |
| Inscription, travail, absence (2) | `Inscription (present, travailRealise)` | ✅ |
| Réunions (0,5), convocations (1,5), ordre du jour ordonné (3) | `Reunion`, `Convocation`, `PointOrdreJour (numOrdre)` | ✅ |
| Diagramme, jeu d'essai (2) | `docs/diagramme-ap3.md` + diagramme SSMS ; `php spark db:seed RobotixSeeder` | ✅ |
| Sauvegarde, script SQL (2) | `powershell -ExecutionPolicy Bypass -File tools\sauvegarde-sql.ps1` → `docs/sql/robotix58.sql` (ou SSMS : Tâches > Générer des scripts) | ✅ |

### Programmation de la base (/40)

Tous les résultats sont visibles sur **Compte → Rapports SQL Server** (`/admin/club/rapports`), avec la requête `EXEC` affichée.

| Objet (pts) | Démonstration | Résultat attendu avec le jeu d'essai |
|---|---|---|
| `ps_AdherentsRenouveles @annee` (2) | Année 2026 | Durand, Martin, Michel, Petit, Robert |
| `ps_OrdreDuJour @date` (3) | Date 30/09/2026 | 3 points dans l'ordre, réunion de 18:00 |
| `ps_EvenementsSuivis @idAdherent` (5) | Camille Martin | 3 événements : Présent, Présent, Non pointé, avec le travail réalisé |
| `ps_NbEvenementsEntreDates … @nb OUTPUT` (5) | Lucas Bernard, 01/09 → 30/09/2026 | 2 |
| `ps_HeuresEntrainement` (5) | — | Lucas 5,00 h en tête |
| `trg_Inscription_Mail` (5) | Espace membre de Camille → « S'inscrire » à un événement | Nouveau mail « Inscription confirmée : … » dans les rapports |
| `trg_Inscription_NbEvenements` (5) | Même inscription | Le nombre d'événements de Camille augmente dans l'espace membre |
| `trg_Utilisateur_Historisation` (5) | Adhérents → modifier Jules Moreau → décocher « Compte actif » | Ligne « Adhésion non renouvelée » (dernière adhésion 2025) dans les rapports |
| `v_EvenementsPresents` (2,5) | Rapports | 8 présences réparties sur 3 événements |
| `v_AdherentsRoles` (2,5) | Rapports | 3 Entraîneur + 9 Joueur |

## Développement du site avec CodeIgniter (/60)

### Modélisation (/30)

| Critère (pts) | Où le montrer | Résultat |
|---|---|---|
| Vues (pages) et routages (8) | `app/Config/Routes.php` (groupes `club`, `admin`) ; `app/Views/club/*`, `app/Views/admin/*` | ✅ |
| Modèles (tables et vues) et tests ; affichage d'une table (5) | `MembreModel`, `AnimateurModel`, `InscriptionModel`, `ReunionModel`, `TypeEvenementModel`, `MailModel`, `VueAdherentsRolesModel`, `VueEvenementsPresentsModel` ; `tests/database/ModelesAp3Test.php` | ✅ |
| Clés étrangères, tests (5) | Inscription à un événement inconnu / passé, remplaçant non animateur, suppression d'un animateur | ✅ |
| Tables de relations (4) | `Inscription`, `Convocation` (`ReunionModel::convoques()`), `ClientInteret` | ✅ |
| Héritage, tests (4) | `MembreModel::complet()`, `AnimateurModel::complet()`, `devenirMembre()` refusé pour un non-client | ✅ |
| Authentification par mail ou pseudo (4) | `/compte/connexion` avec `camille` ou `client@robotix.test` | ✅ |

### Programmation (/30)

| Critère (pts) | Où le montrer | Résultat |
|---|---|---|
| Redirection (6) | Connexion : admin → rapports, `karim.h` → espace animateur, `camille` → espace membre, Jules → profil ; page protégée → connexion → retour à la page demandée | ✅ |
| Page invité (3) | `/club` (sans connexion) : présentation, chiffres, animateurs, prochains ateliers | ✅ |
| Page joueur (5) | `/club/membres` : liste des joueurs, événements à venir avec inscription, mes événements suivis | ✅ |
| Page entraîneur (4) | `/club/animateur` : événements animés ou en remplacement, présence et travail réalisé | ✅ |
| Organisation — remplacement des entraîneurs (8) | `/admin/club/animateurs` : nommer, modifier spécialité et remplaçant, retirer (événements confiés au remplaçant) ; `/admin/evenements` : « Faire remplacer » | ✅ |
| Organisation — événements (4) | `/admin/evenements` : CRUD avec type (table) et animateur | ✅ |

## Preuves

- Tests : `php vendor/bin/phpunit --no-coverage` ; `node --test "tests/js/*.test.js"`.
- W3C : `powershell -ExecutionPolicy Bypass -File tools\w3c.ps1`.
- Script SQL : `docs/sql/robotix58.sql`.
