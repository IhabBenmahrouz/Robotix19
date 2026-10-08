# Robotix19 — AP3 « Club Robotix » (SQL Server + CodeIgniter) : document de design

- **Date :** 2026-10-05
- **Auteur :** Ihab Benmahrouz (BTS SIO — AP3)
- **Statut :** validé en discussion, en attente de relecture
- **Prérequis :** AP1 et AP2 livrés (`2026-10-01-robotix19-design.md`, `2026-10-05-robotix19-ap2-design.md`)

## 1. Objectif

Couvrir la grille d'évaluation AP3 (programmation SQL Server /60 et développement CodeIgniter /60) en transformant l'espace adhérents de Robotix en **Club Robotix** : des **joueurs** (membres qui suivent des ateliers et démonstrations) et des **entraîneurs** (animateurs qui encadrent les événements), tous deux adhérents ; des **réunions internes** entre animateurs avec ordre du jour.

Vocabulaire affiché sur le site : « membre » (joueur), « animateur » (entraîneur), « organisation » (administrateur). Les noms d'objets SQL et la recette reprennent les termes de la grille.

### Correspondance avec la grille

| Critère (pts) | Réalisation |
|---|---|
| Gestion des adhérents, héritage (5) | `Utilisateur → Client → Membre / Animateur` (tables filles partageant la clé `idUtilisateur`) |
| Événement, Type, Animateur, Lieu (4 × 0,5) | `Evenement`, `TypeEvenement` (nouvelle), `Animateur`, `Showroom` |
| Animateur remplaçant, réflexivité (2) | `Animateur.idRemplacant → Animateur.idUtilisateur` |
| Inscription, travail, absence (2) | `Inscription (idMembre, idEvenement, present, travailRealise)` |
| Réunions (0,5), convocations entraîneur (1,5), ordre du jour ordonné (3) | `Reunion`, `Convocation`, `PointOrdreJour (numOrdre)` |
| Diagramme, jeu d'essai (2) | `docs/diagramme-ap3.md` (Mermaid) + diagramme SSMS ; `RobotixSeeder` complété |
| Sauvegarde, script SQL (2) | `tools/sauvegarde-sql.ps1` (SMO / SQLPS) → `docs/sql/robotix58.sql` |
| 5 procédures stockées (20) | §3 |
| 3 déclencheurs (15) | §3 |
| 2 vues (5) | §3 |
| Vues (pages) et routages (8) | §5 |
| Modèles (tables et vues) et tests ; affichage d'une table (5) | modèles CodeIgniter pour chaque table et vue, tests PHPUnit |
| Clés étrangères, tests (5) | inscriptions, animateur d'un événement, remplaçant ; refus testés |
| Tables de relations (4) | `Inscription`, `Convocation`, `ClientInteret` |
| Héritage, tests (4) | `AnimateurModel` / `MembreModel` reconstituent l'objet complet ; création et suppression en cascade testées |
| Authentification par mail ou pseudo (4) | champ « E-mail ou pseudo » |
| Redirection (6) | après connexion selon le rôle ; filtres `membre`, `animateur`, `admin` ; retour à la page demandée |
| Page invité (3), page joueur (5), page entraîneur (4) | `/club`, `/club/membres`, `/club/animateur` |
| Page organisation avec CRUD : remplacement des entraîneurs (8), événements (4) | `/admin/club/animateurs`, `/admin/evenements` |

### Hors périmètre

- Envoi réel des mails (le déclencheur les écrit dans `MailAEnvoyer`, consultable dans l'administration).
- Pages de gestion des réunions : les réunions, convocations et ordres du jour sont des données (jeu d'essai) exploitées par les procédures ; l'administration affiche l'ordre du jour d'une date via `ps_OrdreDuJour`.

## 2. Données (migration `CreateClubAp3`)

```sql
ALTER TABLE Utilisateur ADD pseudo VARCHAR(30) NULL;
CREATE UNIQUE INDEX UX_Utilisateur_pseudo ON Utilisateur(pseudo) WHERE pseudo IS NOT NULL;

Membre (idUtilisateur INT PK FK Client,
        niveau VARCHAR(20) NOT NULL CHECK IN ('débutant','intermédiaire','confirmé') DEFAULT 'débutant',
        nbEvenements INT NOT NULL DEFAULT 0)
Animateur (idUtilisateur INT PK FK Client, specialite VARCHAR(80) NOT NULL,
           idRemplacant INT NULL FK Animateur(idUtilisateur), CHECK (idRemplacant <> idUtilisateur))
TypeEvenement (code VARCHAR(20) PK, libelle VARCHAR(40) NOT NULL)      -- demo, lancement, salon, atelier
ALTER TABLE Evenement ADD idAnimateur INT NULL FK Animateur(idUtilisateur);
ALTER TABLE Evenement ADD CONSTRAINT FK_Evenement_Type FOREIGN KEY (type) REFERENCES TypeEvenement(code);
Inscription (idMembre INT FK Membre, idEvenement INT FK Evenement,
             dateInscription DATETIME NOT NULL DEFAULT GETDATE(),
             present BIT NULL,                 -- NULL = pas encore pointé
             travailRealise VARCHAR(300) NULL,
             PK (idMembre, idEvenement))
Reunion (idReunion INT IDENTITY PK, dateReunion DATETIME NOT NULL, objet VARCHAR(150) NOT NULL,
         idShowroom INT NULL FK Showroom)
Convocation (idReunion INT FK Reunion, idAnimateur INT FK Animateur,
             dateEnvoi DATETIME NOT NULL DEFAULT GETDATE(), PK (idReunion, idAnimateur))
PointOrdreJour (idReunion INT FK Reunion, numOrdre INT NOT NULL CHECK (numOrdre >= 1),
                libelle VARCHAR(200) NOT NULL, PK (idReunion, numOrdre))
MailAEnvoyer (idMail INT IDENTITY PK, destinataire VARCHAR(150) NOT NULL, objet VARCHAR(150) NOT NULL,
              corps VARCHAR(2000) NOT NULL, dateCreation DATETIME NOT NULL DEFAULT GETDATE(),
              envoye BIT NOT NULL DEFAULT 0)
HistoriqueAdhesion (idHistorique INT IDENTITY PK, idUtilisateur INT NOT NULL, nom VARCHAR(50), prenom VARCHAR(50),
                    derniereAnnee INT NULL, motif VARCHAR(100) NOT NULL,
                    dateHistorisation DATETIME NOT NULL DEFAULT GETDATE())
```

`HistoriqueAdhesion` ne porte pas de clé étrangère : l'historique doit survivre à la suppression du compte.

Règles de gestion :

- **RG1** — Un client peut être membre, animateur, les deux ou aucun ; un administrateur ou un rédacteur n'est ni l'un ni l'autre.
- **RG2** — Un animateur ne peut pas être son propre remplaçant ; supprimer un animateur remplace par `NULL` le remplaçant des autres et l'animateur des événements concernés.
- **RG3** — Seul un membre s'inscrit à un événement, une seule fois ; la présence (`present`) et le travail sont saisis par l'animateur de l'événement (ou son remplaçant) ou par l'administration.
- **RG4** — « Heures d'entraînement » = durée des événements de type `atelier` où le membre est présent.
- **RG5** — Adhésion renouvelée pour l'année N = adhésion en N **et** en N − 1.
- **RG6** — Le pseudo est facultatif, unique, 3 à 30 caractères `[A-Za-z0-9_.-]`.

### Jeu d'essai

- 3 animateurs (nouveaux comptes clients) : Karim Haddad (Programmation), Inès Fontaine (Robotique domestique), Théo Garnier (Robots éducatifs) ; remplaçants : Karim ← Inès, Inès ← Théo, Théo ← Karim. Pseudos : `karim.h`, `ines.f`, `theo.g`.
- 9 des 12 adhérents deviennent membres (niveaux variés) ; pseudo `camille` pour `client@robotix.test`.
- Chaque événement reçoit un animateur ; ateliers et démonstrations de 2026 avec inscriptions, présences passées (événements antérieurs ajoutés en septembre 2026 pour avoir des présences) et travaux réalisés.
- 2 réunions (une passée, une à venir) avec 2 à 3 convocations et 3 à 4 points d'ordre du jour chacune.

## 3. Programmation SQL Server (migration `CreateProgrammationAp3`)

Toutes les définitions sont créées par `CREATE OR ALTER` dans une migration (rejouable) et exportées dans le script de sauvegarde.

### Procédures stockées

| Procédure | Paramètres | Résultat |
|---|---|---|
| `ps_AdherentsRenouveles` | `@annee INT` | idUtilisateur, nom, prenom, email, formule N−1, formule N |
| `ps_OrdreDuJour` | `@date DATE` | objet de la réunion, heure, numOrdre, libelle — trié par heure puis numOrdre |
| `ps_EvenementsSuivis` | `@idAdherent INT` | nom, prenom, titre, dateDebut, présence (`Présent` / `Absent` / `Non pointé`), travailRealise — trié par date |
| `ps_NbEvenementsEntreDates` | `@idMembre INT, @debut DATE, @fin DATE, @nb INT OUTPUT` | `@nb` = événements où le membre est présent, dont la date est entre @debut et @fin inclus ; renvoie aussi le jeu d'une ligne `nb` |
| `ps_HeuresEntrainement` | — | idUtilisateur, nom, prenom, heures (DECIMAL(6,2)) pour chaque membre, 0 si aucune — trié par heures décroissantes |

### Déclencheurs

| Déclencheur | Événement | Effet |
|---|---|---|
| `trg_Inscription_Mail` | `AFTER INSERT ON Inscription` | une ligne `MailAEnvoyer` par inscription : destinataire = e-mail du membre, objet « Inscription confirmée : <titre> », corps avec la date et le lieu (gère les insertions multiples) |
| `trg_Inscription_NbEvenements` | `AFTER INSERT, UPDATE, DELETE ON Inscription` | recalcule `Membre.nbEvenements` (nombre d'inscriptions) pour les membres touchés |
| `trg_Utilisateur_Historisation` | `AFTER UPDATE ON Utilisateur` | pour chaque adhérent passé de `actif = 1` à `0` sans adhésion pour l'année en cours : copie (id, nom, prénom, dernière année d'adhésion, motif « Adhésion non renouvelée ») dans `HistoriqueAdhesion` |

### Vues

| Vue | Colonnes |
|---|---|
| `v_EvenementsPresents` | idEvenement, titre, dateDebut, type, idUtilisateur, nom, prenom, travailRealise — inscriptions avec `present = 1` |
| `v_AdherentsRoles` | idUtilisateur, nom, prenom, email, role (`Entraîneur` / `Joueur`), detail (spécialité ou niveau) — un animateur membre apparaît deux fois |

## 4. Modèles CodeIgniter

| Modèle | Rôle |
|---|---|
| `TypeEvenementModel` | table `TypeEvenement` (remplace la constante `EvenementModel::TYPES` comme source) |
| `MembreModel` | table `Membre` ; `complets()` (Utilisateur + Client + Membre), `creer(idUtilisateur, niveau)` |
| `AnimateurModel` | table `Animateur` ; `complets()` avec le nom du remplaçant, `definirRemplacant()`, `supprimerAnimateur()` (RG2) |
| `InscriptionModel` | table de relation ; `inscrire()`, `pointer(idMembre, idEvenement, present, travail)`, `inscritsDe(idEvenement)` |
| `ReunionModel` | réunions, convocations et ordre du jour (lecture) |
| `VueEvenementsPresentsModel`, `VueAdherentsRolesModel` | modèles en lecture seule sur les vues |
| `ProceduresAp3` (bibliothèque) | appels des procédures (`EXEC … @param = ?`) et récupération du paramètre `OUTPUT` |
| `MailModel` | lecture de `MailAEnvoyer` |

## 5. Pages et redirections

| Route | Accès | Contenu |
|---|---|---|
| `GET /club` | public (invité) | Présentation du club, animateurs (spécialité), prochains ateliers, chiffres (membres, heures d'atelier) |
| `GET /club/membres` | membre | Liste des joueurs (niveau, nombre d'événements) et des événements à venir avec bouton « S'inscrire » / « Inscrit » ; mes événements suivis (`ps_EvenementsSuivis`) |
| `POST /club/inscription` | membre | Inscription à un événement |
| `GET /club/animateur` | animateur | Événements qu'il anime ou dont il est remplaçant de l'animateur titulaire ; pour chacun, liste des inscrits |
| `POST /club/animateur/evenements/{id}/presences` | animateur | Enregistre présence et travail réalisé de chaque inscrit |
| `GET /admin/club/animateurs` | admin | Liste des animateurs avec remplaçant ; création (depuis un client), modification (spécialité, remplaçant), suppression |
| `POST /admin/evenements/{id}/remplacer` | admin | L'événement passe à l'animateur remplaçant du titulaire |
| `GET /admin/club/rapports` | admin | Résultats des 5 procédures (formulaires de paramètres), des 2 vues et des mails générés |
| `/admin/evenements` | admin | CRUD existant complété par le type (liste issue de `TypeEvenement`) et l'animateur |

Redirection après connexion : vers la page demandée si l'accès venait d'un filtre, sinon admin → `/admin/club/rapports`, animateur → `/club/animateur`, membre → `/club/membres`, autre client → `/compte/profil`, rédacteur → `/`.

Session : `role` inchangé (admin, client, rédacteur) + `profilClub` = liste parmi `membre`, `animateur`. Filtres `membre` et `animateur`.

## 6. Erreurs et sécurité

- Procédures et requêtes paramétrées ; pas de SQL dynamique.
- Pointage refusé si l'animateur connecté n'est ni le titulaire ni le remplaçant du titulaire.
- Inscription refusée : événement passé, complet (`nbPlaces` atteint, inscriptions + panier) ou déjà inscrit.
- CSRF et `esc()` comme dans le reste du site.

## 7. Tests

- PHPUnit : un test par procédure (valeurs du jeu d'essai), par déclencheur (insertion, mise à jour, suppression ; insertions multiples), par vue ; modèles (héritage, clés étrangères, tables de relation) ; pages (accès par rôle, redirections, connexion par pseudo, pointage, remplacement).
- W3C : nouvelles pages ajoutées au script (publiques et exportées).
- Recette `docs/recette-ap3.md` reprenant la grille.

## 8. Découpage

1. Migration des tables AP3, jeu d'essai, diagramme.
2. Procédures, déclencheurs, vues (migration T-SQL) + tests.
3. Modèles CodeIgniter (tables, vues, héritage, relations) + tests.
4. Connexion par pseudo, rôles du club, filtres, redirections.
5. Pages invité, membre, animateur.
6. Organisation : animateurs et remplacement, événements, rapports.
7. Sauvegarde SQL, W3C, recette.
