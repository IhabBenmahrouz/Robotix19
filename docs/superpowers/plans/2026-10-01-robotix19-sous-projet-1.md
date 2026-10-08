# Robotix19 — Sous-projet 1 (socle + grille AP1) : plan d'implémentation

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal :** Livrer un site Robotix (CodeIgniter 4 + SQL Server) qui couvre les 15 critères de la grille AP1 : gabarit responsive, accueil, image réactive, Store en consultation, tarifs avec calculateur, calendrier et gestion du planning, Google Maps, contact et inscription contrôlés, W3C et portfolio.

**Architecture :** MVC CodeIgniter 4. Contrôleurs fins, requêtes dans les modèles, vues avec un layout unique `layouts/main.php`. JavaScript vanilla, un fichier par fonctionnalité. Chaque fichier JS expose ses fonctions pures pour Node (`module.exports`), ce qui permet de les tester avec `node --test`. La partie DOM ne s'exécute que dans le navigateur.

**Tech Stack :** PHP 8.1 (Laragon), CodeIgniter 4.6.5, SQL Server (pilote SQLSRV), PHPUnit 10, Node 24 (`node:test`), Bootstrap 5.3.3 (CDN jsDelivr), Google Fonts (Orbitron, Inter), Google Maps en `iframe` (sans clé).

**Spec :** `docs/superpowers/specs/2026-10-01-robotix19-design.md`

## Global Constraints

- Dossier du projet : `C:\laragon\www\Robotix19`. URL : `http://localhost/Robotix19/`.
- PHP en ligne de commande : avant toute commande, dans PowerShell, `$env:Path = "C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64;$env:Path"`.
- Base : `Robotix58` sur `WIN-CIL97SHCG0U:1433`, utilisateur `IhabBen`, pilote `SQLSRV`, sans préfixe. Les tests utilisent la même base, dans une transaction annulée à la fin de chaque test.
- Dates envoyées à SQL Server : format ISO `Y-m-d\TH:i:s` (fonction `date_sql()`) ou `GETDATE()`. **Jamais** `date('Y-m-d H:i:s')`, qui est mal interprété quand la langue de SQL Server est le français.
- SQL brut : `TOP N` (pas de `LIMIT`), pas de `AS` devant l'alias d'une table dérivée, toutes les colonnes non agrégées dans le `GROUP BY`.
- `$this->db` uniquement dans les modèles ; `db_connect()` ailleurs ; `service('request')` dans les vues.
- Toute donnée affichée passe par `esc()`. Tout formulaire POST contient `csrf_field()`.
- Interface et messages en français, avec les accents.
- TVA : 20 % (`tauxTva = 20.00` en base, exprimé en pourcentage).
- Comptes de démo : `client@robotix.test`, `redacteur@robotix.test`, `admin@robotix.test`, mot de passe `Robotix2026!`.
- Pas de dépôt git (l'utilisateur ne l'a pas demandé) : les étapes « Commit » sont remplacées par un point de contrôle (tests verts).

## Review Focus

1. **Dates au-delà du 12 du mois :** un événement créé le `2026-11-25T14:00` doit être enregistré au 25 novembre, et non provoquer une erreur ou une inversion jour/mois (test dans la tâche 13).
2. **Accents dans les saisies :** une inscription au nom de « Hélène Châtelet » doit être relue identique depuis la base (test dans la tâche 12).
3. **E-mail en majuscules :** `CLIENT@Robotix.test` doit être refusé à l'inscription (doublon) et accepté à la connexion (tests dans la tâche 12).
4. **Balises HTML saisies par un admin :** un titre d'événement `<script>alert(1)</script>` doit s'afficher échappé dans l'admin (test dans la tâche 13).
5. **Quantité absurde dans le calculateur :** `0`, `-3`, `abc` ou `99` sont ramenés dans l'intervalle 1 à 5 et signalés (test dans la tâche 7).

---

### Task 1 : Configuration, helper et socle de tests

**Files :**
- Modify : `app/Config/App.php` (`$indexPage`, `$defaultLocale`, `$appTimezone`)
- Modify : `app/Config/Autoload.php` (`$helpers`)
- Modify : `app/Config/Filters.php` (CSRF global sauf `api/*`)
- Modify : `.env` (ligne `app.baseURL`)
- Create : `phpunit.xml`
- Create : `app/Helpers/robotix_helper.php`
- Create : `tests/_support/RobotixTestCase.php`
- Delete : `tests/database/ExampleDatabaseTest.php`, `tests/session/ExampleSessionTest.php` (l'exemple de test base ferait un `refresh` destructeur sur Robotix58)
- Test : `tests/unit/RobotixHelperTest.php`

**Interfaces :**
- Produces : `euros(float|string $montant): string`, `prix_ttc(float|string $ht, float|string $taux): float`, `date_fr(?string $date, bool $heure = true): string`, `date_sql(string $saisie): string`, `date_saisie(?string $date): string`, `menu_actif(string $segment): bool`.
- Produces : `Tests\Support\RobotixTestCase` (FeatureTestTrait, transaction annulée, CSRF désactivé, `$this->db`, `idProduit(string $reference): int`, `idUtilisateur(string $email): int`, `sessionDe(string $email): array`).

- [ ] **Step 1 : Configurer l'application**

Dans `app/Config/App.php` :

```php
public string $indexPage = '';
public string $defaultLocale = 'fr';
public string $appTimezone = 'Europe/Paris';
```

Dans `app/Config/Autoload.php` :

```php
public $helpers = ['url', 'form', 'robotix'];
```

Dans `app/Config/Filters.php`, tableau `$globals` :

```php
public array $globals = [
    'before' => [
        'csrf' => ['except' => ['api/*']],
    ],
    'after' => [],
];
```

Dans `.env`, remplacer la ligne 23 par :

```ini
app.baseURL = 'http://localhost/Robotix19/'
```

- [ ] **Step 2 : Configurer PHPUnit sur Robotix58**

Copier `phpunit.xml.dist` en `phpunit.xml`, puis remplacer le bloc commenté « Database configuration » par :

```xml
        <!-- Base de test : Robotix58 (chaque test est annulé par transaction) -->
        <env name="database.tests.hostname" value="WIN-CIL97SHCG0U"/>
        <env name="database.tests.database" value="Robotix58"/>
        <env name="database.tests.username" value="IhabBen"/>
        <env name="database.tests.password" value="(mot de passe local)"/>
        <env name="database.tests.DBDriver" value="SQLSRV"/>
        <env name="database.tests.DBPrefix" value=""/>
        <env name="database.tests.port" value="1433"/>
```

Supprimer les exemples :

```powershell
Remove-Item tests\database\ExampleDatabaseTest.php, tests\session\ExampleSessionTest.php
```

- [ ] **Step 3 : Écrire la classe de base des tests**

`tests/_support/RobotixTestCase.php` :

```php
<?php

namespace Tests\Support;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Base des tests Robotix : chaque test tourne dans une transaction
 * annulée à la fin, la base Robotix58 retrouve donc son état d'origine.
 */
abstract class RobotixTestCase extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected BaseConnection $db;

    protected function setUp(): void
    {
        parent::setUp();

        // Les formulaires sont testés sans jeton CSRF
        unset(config('Filters')->globals['before']['csrf']);

        $this->db = db_connect();
        $this->db->transBegin();
    }

    protected function tearDown(): void
    {
        $this->db->transRollback();
        parent::tearDown();
    }

    protected function idProduit(string $reference): int
    {
        return (int) $this->db->table('Produit')->select('idProduit')
            ->where('reference', $reference)->get()->getRow('idProduit');
    }

    protected function idUtilisateur(string $email): int
    {
        return (int) $this->db->table('Utilisateur')->select('idUtilisateur')
            ->where('email', $email)->get()->getRow('idUtilisateur');
    }

    /** Données de session d'un compte de démo, pour withSession(). */
    protected function sessionDe(string $email): array
    {
        $roles = ['admin@robotix.test' => 'admin', 'redacteur@robotix.test' => 'redacteur'];

        return [
            'idUtilisateur' => $this->idUtilisateur($email),
            'nom'           => 'Test',
            'prenom'        => 'Test',
            'role'          => $roles[$email] ?? 'client',
        ];
    }
}
```

- [ ] **Step 4 : Écrire le test du helper (échoue)**

`tests/unit/RobotixHelperTest.php` :

```php
<?php

use CodeIgniter\Test\CIUnitTestCase;

final class RobotixHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('robotix');
    }

    public function testEurosFormateALaFrancaise(): void
    {
        $this->assertSame('1 234,50 €', euros(1234.5));
        $this->assertSame('0,00 €', euros('0'));
    }

    public function testPrixTtcAppliqueLaTva(): void
    {
        $this->assertSame(16680.0, prix_ttc('13900.00', '20.00'));
    }

    public function testDateFrLitLeFormatSqlServer(): void
    {
        $this->assertSame('15/10/2026 14:00', date_fr('2026-10-15 14:00:00.000'));
        $this->assertSame('15/10/2026', date_fr('2026-10-15 14:00:00.000', false));
        $this->assertSame('', date_fr(null));
    }

    public function testDateSqlProduitUnFormatIsoNonAmbigu(): void
    {
        $this->assertSame('2026-11-25T14:30:00', date_sql('2026-11-25T14:30'));
    }

    public function testDateSaisiePourChampDatetimeLocal(): void
    {
        $this->assertSame('2026-11-25T14:30', date_saisie('2026-11-25 14:30:00.000'));
        $this->assertSame('', date_saisie(null));
    }
}
```

- [ ] **Step 5 : Lancer le test pour vérifier qu'il échoue**

Run : `php vendor/bin/phpunit tests/unit/RobotixHelperTest.php`
Expected : FAIL (« Unable to load the requested file: helpers/robotix_helper.php »).

- [ ] **Step 6 : Écrire le helper**

`app/Helpers/robotix_helper.php` :

```php
<?php

/**
 * Fonctions utilitaires Robotix : prix, dates et menu.
 */

if (! function_exists('euros')) {
    /** 1234.5 → « 1 234,50 € » */
    function euros(float|string $montant): string
    {
        return number_format((float) $montant, 2, ',', ' ') . ' €';
    }
}

if (! function_exists('prix_ttc')) {
    /** Prix TTC arrondi au centime ; $taux est un pourcentage (20 = 20 %). */
    function prix_ttc(float|string $ht, float|string $taux): float
    {
        return round((float) $ht * (1 + (float) $taux / 100), 2);
    }
}

if (! function_exists('date_fr')) {
    /** « 2026-10-15 14:00:00.000 » (format SQL Server) → « 15/10/2026 14:00 » */
    function date_fr(?string $date, bool $heure = true): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        return date($heure ? 'd/m/Y H:i' : 'd/m/Y', strtotime(substr($date, 0, 19)));
    }
}

if (! function_exists('date_sql')) {
    /**
     * Saisie (« 2026-11-25T14:30 » ou « 2026-11-25 14:30:00 ») → « 2026-11-25T14:30:00 ».
     * Le format ISO avec « T » est compris par SQL Server quelle que soit sa langue.
     */
    function date_sql(string $saisie): string
    {
        return date('Y-m-d\TH:i:s', strtotime($saisie));
    }
}

if (! function_exists('date_saisie')) {
    /** Date de la base → valeur d'un champ <input type="datetime-local">. */
    function date_saisie(?string $date): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        return date('Y-m-d\TH:i', strtotime(substr($date, 0, 19)));
    }
}

if (! function_exists('menu_actif')) {
    /** Vrai si le premier segment de l'URL courante vaut $segment ('' = accueil). */
    function menu_actif(string $segment): bool
    {
        return service('request')->getUri()->getSegment(1) === $segment;
    }
}
```

- [ ] **Step 7 : Lancer le test pour vérifier qu'il passe**

Run : `php vendor/bin/phpunit tests/unit/RobotixHelperTest.php`
Expected : PASS (5 tests).

- [ ] **Step 8 : Point de contrôle**

Run : `php vendor/bin/phpunit`
Expected : PASS (le `HealthTest` d'origine passe aussi).

---

### Task 2 : Migration des tables Showroom, Evenement et MessageContact

**Files :**
- Create : `app/Database/Migrations/2026-10-01-000001_CreateShowroomEvenementContact.php`
- Test : `tests/database/MigrationTest.php`

**Interfaces :**
- Produces : tables `Showroom`, `Evenement`, `MessageContact` (colonnes exactes dans la spec §5).

- [ ] **Step 1 : Écrire le test (échoue)**

`tests/database/MigrationTest.php` :

```php
<?php

use Tests\Support\RobotixTestCase;

final class MigrationTest extends RobotixTestCase
{
    public function testLesNouvellesTablesExistent(): void
    {
        foreach (['Showroom', 'Evenement', 'MessageContact'] as $table) {
            $this->assertTrue($this->db->tableExists($table, false), "Table $table absente");
        }
    }

    public function testEvenementPossedeSesColonnes(): void
    {
        $colonnes = $this->db->getFieldNames('Evenement');

        foreach (['idEvenement', 'titre', 'description', 'type', 'dateDebut', 'dateFin', 'idShowroom', 'idProduit'] as $colonne) {
            $this->assertContains($colonne, $colonnes);
        }
    }

    public function testMessageContactRempliDateEtTraiteParDefaut(): void
    {
        $this->db->table('MessageContact')->insert([
            'nom' => 'Test', 'email' => 'test@exemple.fr', 'objet' => 'autre',
            'message' => 'Un message de test suffisamment long.',
        ]);
        $ligne = $this->db->table('MessageContact')->where('email', 'test@exemple.fr')->get()->getRowArray();

        $this->assertNotEmpty($ligne['dateEnvoi']);
        $this->assertSame(0, (int) $ligne['traite']);
    }
}
```

- [ ] **Step 2 : Lancer le test pour vérifier qu'il échoue**

Run : `php vendor/bin/phpunit tests/database/MigrationTest.php`
Expected : FAIL (« Table Showroom absente »).

- [ ] **Step 3 : Écrire la migration**

`app/Database/Migrations/2026-10-01-000001_CreateShowroomEvenementContact.php` :

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Ajoute à Robotix58 les tables nécessaires à la grille AP1 :
 * showrooms (Google Maps), événements (calendrier) et messages de contact.
 */
class CreateShowroomEvenementContact extends Migration
{
    public function up(): void
    {
        $this->db->query('CREATE TABLE Showroom (
            idShowroom  INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_Showroom PRIMARY KEY,
            nom         VARCHAR(100) NOT NULL,
            adresse     VARCHAR(200) NOT NULL,
            codePostal  VARCHAR(5)   NOT NULL,
            ville       VARCHAR(100) NOT NULL,
            latitude    DECIMAL(9,6) NOT NULL,
            longitude   DECIMAL(9,6) NOT NULL,
            telephone   VARCHAR(20)  NULL,
            horaires    VARCHAR(200) NULL,
            image       VARCHAR(255) NULL
        )');

        $this->db->query("CREATE TABLE Evenement (
            idEvenement INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_Evenement PRIMARY KEY,
            titre       VARCHAR(150)  NOT NULL,
            description VARCHAR(1000) NULL,
            type        VARCHAR(20)   NOT NULL
                CONSTRAINT CK_Evenement_type CHECK (type IN ('demo', 'lancement', 'salon', 'atelier')),
            dateDebut   DATETIME NOT NULL,
            dateFin     DATETIME NOT NULL,
            idShowroom  INT NULL CONSTRAINT FK_Evenement_Showroom REFERENCES Showroom(idShowroom),
            idProduit   INT NULL CONSTRAINT FK_Evenement_Produit REFERENCES Produit(idProduit),
            CONSTRAINT CK_Evenement_dates CHECK (dateFin > dateDebut)
        )");

        $this->db->query("CREATE TABLE MessageContact (
            idMessage   INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_MessageContact PRIMARY KEY,
            nom         VARCHAR(100)  NOT NULL,
            email       VARCHAR(150)  NOT NULL,
            telephone   VARCHAR(20)   NULL,
            objet       VARCHAR(20)   NOT NULL
                CONSTRAINT CK_MessageContact_objet CHECK (objet IN ('demo', 'devis', 'sav', 'autre')),
            idProduit   INT NULL CONSTRAINT FK_MessageContact_Produit REFERENCES Produit(idProduit),
            message     VARCHAR(2000) NOT NULL,
            dateEnvoi   DATETIME NOT NULL CONSTRAINT DF_MessageContact_date DEFAULT GETDATE(),
            traite      BIT      NOT NULL CONSTRAINT DF_MessageContact_traite DEFAULT 0
        )");
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE MessageContact');
        $this->db->query('DROP TABLE Evenement');
        $this->db->query('DROP TABLE Showroom');
    }
}
```

- [ ] **Step 4 : Appliquer la migration**

Run : `php spark migrate`
Expected : « Migrations complete. »

- [ ] **Step 5 : Lancer le test pour vérifier qu'il passe**

Run : `php vendor/bin/phpunit tests/database/MigrationTest.php`
Expected : PASS (3 tests).

- [ ] **Step 6 : Point de contrôle** — `php vendor/bin/phpunit` : tout est vert.

---
### Task 3 : Illustrations SVG et données de démonstration

**Files :**
- Create : `app/Libraries/IllustrationRobot.php`
- Create : `app/Database/Seeds/RobotixSeeder.php`
- Test : `tests/unit/IllustrationRobotTest.php`, `tests/database/SeederTest.php`

**Interfaces :**
- Produces : `IllustrationRobot::robot(string $couleur, int $variante): string` (SVG 400×500 ; variante 1 = bras le long du corps, 2 = bras droit levé) et `IllustrationRobot::logo(string $initiales, string $couleur): string` (SVG 200×80).
- Produces : les données de la spec §5. Références produits : `RBX-UNI-G1`, `RBX-UNI-H1`, `RBX-UNI-R1`, `RBX-FIG-02`, `RBX-FIG-03`, `RBX-AGI-DGT`, `RBX-1X-NEO`, `RBX-1X-EVE`, `RBX-POL-R2`, `RBX-POL-MINI`. Images : `public/images/robots/<reference en minuscules>-1.svg` et `-2.svg` (`numOrdre` 1 et 2), plus `defaut.svg`. Événements : 4 en octobre, 4 en novembre et 4 en décembre 2026.

- [ ] **Step 1 : Écrire le test de l'illustration (échoue)**

`tests/unit/IllustrationRobotTest.php` :

```php
<?php

use App\Libraries\IllustrationRobot;
use CodeIgniter\Test\CIUnitTestCase;

final class IllustrationRobotTest extends CIUnitTestCase
{
    public function testRobotEstUnSvgALaCouleurDemandee(): void
    {
        $svg = IllustrationRobot::robot('#00d4ff', 1);

        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringContainsString('#00d4ff', $svg);
        $this->assertStringContainsString('viewBox="0 0 400 500"', $svg);
    }

    public function testLesDeuxVariantesSontDifferentes(): void
    {
        $this->assertNotSame(IllustrationRobot::robot('#00d4ff', 1), IllustrationRobot::robot('#00d4ff', 2));
    }

    public function testCouleurInvalideRefusee(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IllustrationRobot::robot('red"><script>', 1);
    }

    public function testLogoContientLesInitiales(): void
    {
        $this->assertStringContainsString('>UR<', IllustrationRobot::logo('UR', '#00d4ff'));
    }
}
```

- [ ] **Step 2 : Lancer le test pour vérifier qu'il échoue**

Run : `php vendor/bin/phpunit tests/unit/IllustrationRobotTest.php`
Expected : FAIL (« Class "App\Libraries\IllustrationRobot" not found »).

- [ ] **Step 3 : Écrire la bibliothèque d'illustrations**

`app/Libraries/IllustrationRobot.php` :

```php
<?php

namespace App\Libraries;

use InvalidArgumentException;

/**
 * Génère les visuels SVG des robots et des marques (libres de droits,
 * vectoriels donc nets à toutes les tailles d'écran).
 */
final class IllustrationRobot
{
    public static function robot(string $couleur, int $variante): string
    {
        self::verifierCouleur($couleur);

        $brasDroit = $variante === 2
            ? '<rect x="273" y="60" width="32" height="125" rx="16" fill="#cbd5e1"/><circle cx="289" cy="55" r="18" fill="' . $couleur . '"/>'
            : '<rect x="273" y="175" width="32" height="130" rx="16" fill="#cbd5e1"/><circle cx="289" cy="315" r="18" fill="' . $couleur . '"/>';

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 500" width="400" height="500">'
            . '<defs><linearGradient id="fond" x1="0" y1="0" x2="0" y2="1">'
            . '<stop offset="0" stop-color="#0b1020"/><stop offset="1" stop-color="#1e293b"/></linearGradient></defs>'
            . '<rect width="400" height="500" fill="url(#fond)"/>'
            . '<circle cx="200" cy="250" r="170" fill="' . $couleur . '" opacity=".15"/>'
            // Tête
            . '<rect x="150" y="60" width="100" height="90" rx="40" fill="#e2e8f0"/>'
            . '<rect x="165" y="90" width="70" height="30" rx="15" fill="#0b1020"/>'
            . '<circle cx="185" cy="105" r="7" fill="' . $couleur . '"/><circle cx="215" cy="105" r="7" fill="' . $couleur . '"/>'
            . '<rect x="190" y="150" width="20" height="15" fill="#94a3b8"/>'
            // Torse
            . '<rect x="135" y="165" width="130" height="150" rx="30" fill="#e2e8f0"/>'
            . '<circle cx="200" cy="225" r="18" fill="' . $couleur . '"/>'
            // Bras
            . '<rect x="95" y="175" width="32" height="130" rx="16" fill="#cbd5e1"/><circle cx="111" cy="315" r="18" fill="' . $couleur . '"/>'
            . $brasDroit
            // Jambes
            . '<rect x="148" y="320" width="45" height="140" rx="20" fill="#cbd5e1"/>'
            . '<rect x="207" y="320" width="45" height="140" rx="20" fill="#cbd5e1"/>'
            . '<rect x="140" y="455" width="60" height="18" rx="9" fill="' . $couleur . '"/>'
            . '<rect x="200" y="455" width="60" height="18" rx="9" fill="' . $couleur . '"/>'
            . '</svg>';
    }

    public static function logo(string $initiales, string $couleur): string
    {
        self::verifierCouleur($couleur);
        $initiales = htmlspecialchars($initiales, ENT_XML1);

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 80" width="200" height="80">'
            . '<rect width="200" height="80" rx="16" fill="#0b1020"/>'
            . '<text x="100" y="52" font-family="Arial, sans-serif" font-size="34" font-weight="700" text-anchor="middle" fill="' . $couleur . '">' . $initiales . '</text>'
            . '</svg>';
    }

    private static function verifierCouleur(string $couleur): void
    {
        if (! preg_match('/^#[0-9a-f]{6}$/i', $couleur)) {
            throw new InvalidArgumentException("Couleur invalide : $couleur");
        }
    }
}
```

- [ ] **Step 4 : Lancer le test pour vérifier qu'il passe**

Run : `php vendor/bin/phpunit tests/unit/IllustrationRobotTest.php`
Expected : PASS (4 tests).

- [ ] **Step 5 : Écrire le test du seeder (échoue)**

`tests/database/SeederTest.php` :

```php
<?php

use Config\Database;
use Tests\Support\RobotixTestCase;

final class SeederTest extends RobotixTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Database::seeder()->setSilent(true)->call('RobotixSeeder');
    }

    public function testLesVolumesAttendusSontCrees(): void
    {
        $attendus = [
            'Marque' => 5, 'Categorie' => 4, 'Produit' => 10, 'Image' => 20,
            'Showroom' => 3, 'Evenement' => 12, 'Utilisateur' => 3,
            'Client' => 1, 'Redacteur' => 1, 'Administrateur' => 1,
        ];

        foreach ($attendus as $table => $nombre) {
            $this->assertSame($nombre, $this->db->table($table)->countAllResults(), "Table $table");
        }
    }

    public function testLeSeederPeutEtreRejoue(): void
    {
        Database::seeder()->setSilent(true)->call('RobotixSeeder');

        $this->assertSame(10, $this->db->table('Produit')->countAllResults());
    }

    public function testLeMotDePasseAdminEstHache(): void
    {
        $admin = $this->db->table('Utilisateur')->where('email', 'admin@robotix.test')->get()->getRowArray();

        $this->assertTrue(password_verify('Robotix2026!', $admin['motDePasse']));
    }

    public function testAucunChevauchementDansUnMemeShowroom(): void
    {
        $sql = 'SELECT COUNT(*) AS nb FROM Evenement a JOIN Evenement b
                ON a.idShowroom = b.idShowroom AND a.idEvenement < b.idEvenement
                AND a.dateDebut < b.dateFin AND b.dateDebut < a.dateFin';

        $this->assertSame(0, (int) $this->db->query($sql)->getRow('nb'));
    }

    public function testLesImagesSontEcritesSurLeDisque(): void
    {
        $this->assertFileExists(FCPATH . 'images/robots/rbx-uni-g1-1.svg');
        $this->assertFileExists(FCPATH . 'images/robots/defaut.svg');
        $this->assertFileExists(FCPATH . 'images/marques/unitree-robotics.svg');
    }
}
```

- [ ] **Step 6 : Lancer le test pour vérifier qu'il échoue**

Run : `php vendor/bin/phpunit tests/database/SeederTest.php`
Expected : FAIL (classe `RobotixSeeder` introuvable).

- [ ] **Step 7 : Écrire le seeder**

`app/Database/Seeds/RobotixSeeder.php` :

```php
<?php

namespace App\Database\Seeds;

use App\Libraries\IllustrationRobot;
use CodeIgniter\Database\Seeder;

/**
 * Données de démonstration Robotix. Rejouable : vide les tables puis les remplit.
 * Usage : php spark db:seed RobotixSeeder
 */
class RobotixSeeder extends Seeder
{
    private const MOT_DE_PASSE = 'Robotix2026!';

    public function run(): void
    {
        $this->viderTables();
        $marques    = $this->creerMarques();
        $categories = $this->creerCategories();
        $produits   = $this->creerProduits($marques, $categories);
        $this->creerCompatibilites($produits);
        $showrooms = $this->creerShowrooms();
        $this->creerEvenements($showrooms, $produits);
        $this->creerComptes();
    }

    private function viderTables(): void
    {
        // Ordre : des tables qui référencent vers les tables référencées
        $tables = [
            'Journal', 'HistoriqueStatut', 'Paiement', 'Contenir', 'Commande', 'Adresse',
            'Mentionner', 'Aborder', 'Article', 'Suivre', 'Evenement', 'MessageContact', 'Showroom',
            'CompatibiliteProduit', 'Image', 'Produit', 'Categorie', 'Marque', 'Thematique',
            'Client', 'Redacteur', 'Administrateur', 'Utilisateur',
        ];

        foreach ($tables as $table) {
            $this->db->query("DELETE FROM [$table]");
        }
    }

    /** @return array<string, int> nom => idMarque */
    private function creerMarques(): array
    {
        $marques = [
            ['Unitree Robotics', 'UR', '#00d4ff', 'Chine', 'https://www.unitree.com', 'Pionnier chinois des robots à pattes et des humanoïdes accessibles.'],
            ['Figure AI', 'FA', '#a855f7', 'États-Unis', 'https://www.figure.ai', 'Start-up californienne spécialisée dans les humanoïdes polyvalents.'],
            ['Agility Robotics', 'AR', '#f59e0b', 'États-Unis', 'https://www.agilityrobotics.com', 'Concepteur de Digit, robot bipède pensé pour porter et ranger.'],
            ['1X Technologies', '1X', '#10b981', 'Norvège', 'https://www.1x.tech', 'Fabricant norvégien de robots domestiques sûrs et légers.'],
            ['Pollen Robotics', 'PR', '#ef4444', 'France', 'https://www.pollen-robotics.com', 'Entreprise bordelaise à l\'origine des robots open source Reachy.'],
        ];

        $dossier = FCPATH . 'images/marques/';
        is_dir($dossier) || mkdir($dossier, 0775, true);
        $ids = [];

        foreach ($marques as [$nom, $initiales, $couleur, $pays, $site, $description]) {
            $fichier = url_title($nom, '-', true) . '.svg';
            file_put_contents($dossier . $fichier, IllustrationRobot::logo($initiales, $couleur));

            $this->db->table('Marque')->insert([
                'nom' => $nom, 'pays' => $pays, 'siteWeb' => $site,
                'logo' => $fichier, 'description' => $description,
            ]);
            $ids[$nom] = (int) $this->db->insertID();
        }

        return $ids;
    }

    /** @return array<string, int> libellé => idCategorie */
    private function creerCategories(): array
    {
        $categories = [
            'Compagnon'  => 'Des robots de présence et de conversation pour toute la famille.',
            'Domestique' => 'Des robots qui rangent, portent et assistent dans les tâches du quotidien.',
            'Éducatif'   => 'Des robots programmables pour apprendre la robotique et l\'intelligence artificielle.',
            'Premium'    => 'Les humanoïdes les plus avancés, pour les passionnés exigeants.',
        ];
        $ids = [];

        foreach ($categories as $libelle => $description) {
            $this->db->table('Categorie')->insert(['libelle' => $libelle, 'description' => $description]);
            $ids[$libelle] = (int) $this->db->insertID();
        }

        return $ids;
    }

    /** @return array<string, int> référence => idProduit */
    private function creerProduits(array $marques, array $categories): array
    {
        $produits = [
            ['RBX-UNI-G1', 'Unitree G1', 'Compagnon', 'Unitree Robotics', 13900.00, 8, '#00d4ff', 'Compact (1,30 m) et agile, le G1 vous accueille, joue avec les enfants et apprend de nouveaux gestes par démonstration.'],
            ['RBX-UNI-H1', 'Unitree H1', 'Premium', 'Unitree Robotics', 74900.00, 2, '#38bdf8', 'Humanoïde pleine taille (1,80 m) aux moteurs haute puissance : la référence pour les passionnés de robotique.'],
            ['RBX-UNI-R1', 'Unitree R1', 'Éducatif', 'Unitree Robotics', 4900.00, 15, '#22d3ee', 'Le premier humanoïde à petit prix : idéal pour découvrir la programmation de mouvements en famille.'],
            ['RBX-FIG-02', 'Figure 02', 'Domestique', 'Figure AI', 39900.00, 4, '#7c3aed', 'Mains à 16 degrés de liberté et dialogue naturel : Figure 02 range, plie le linge et charge le lave-vaisselle.'],
            ['RBX-FIG-03', 'Figure 03', 'Premium', 'Figure AI', 59900.00, 3, '#a855f7', 'La nouvelle génération Figure : plus légère, plus silencieuse, avec une vision améliorée pour la maison.'],
            ['RBX-AGI-DGT', 'Digit', 'Domestique', 'Agility Robotics', 49900.00, 3, '#f59e0b', 'Bipède robuste conçu pour porter des charges jusqu\'à 16 kg : courses, cartons et rangement du garage.'],
            ['RBX-1X-NEO', 'NEO Gamma', 'Domestique', '1X Technologies', 16500.00, 6, '#10b981', 'Doux, léger et silencieux, NEO Gamma est pensé pour vivre avec vous : ménage léger, rappels et compagnie.'],
            ['RBX-1X-EVE', 'EVE', 'Compagnon', '1X Technologies', 22900.00, 5, '#34d399', 'Robot à roues au buste humanoïde : il circule dans toute la maison et assure une présence rassurante.'],
            ['RBX-POL-R2', 'Reachy 2', 'Éducatif', 'Pollen Robotics', 19900.00, 6, '#ef4444', 'Robot open source français à deux bras, programmable en Python : parfait pour les lycées et les makers.'],
            ['RBX-POL-MINI', 'Reachy Mini', 'Éducatif', 'Pollen Robotics', 349.00, 40, '#f87171', 'Petit robot de bureau expressif et programmable, pour s\'initier à l\'IA dès 10 ans.'],
        ];

        $dossier = FCPATH . 'images/robots/';
        is_dir($dossier) || mkdir($dossier, 0775, true);
        file_put_contents($dossier . 'defaut.svg', IllustrationRobot::robot('#64748b', 1));
        $ids = [];

        foreach ($produits as [$reference, $nom, $categorie, $marque, $prix, $stock, $couleur, $description]) {
            $this->db->table('Produit')->insert([
                'reference' => $reference, 'nom' => $nom, 'description' => $description,
                'prixHt' => $prix, 'stock' => $stock, 'tauxTva' => 20.00,
                'idCategorie' => $categories[$categorie], 'idMarque' => $marques[$marque],
            ]);
            $id = (int) $this->db->insertID();
            $ids[$reference] = $id;

            foreach ([1 => 'Vue de face', 2 => 'En action'] as $ordre => $legende) {
                $fichier = strtolower($reference) . "-$ordre.svg";
                file_put_contents($dossier . $fichier, IllustrationRobot::robot($couleur, $ordre));
                $this->db->table('Image')->insert([
                    'fichier' => $fichier, 'legende' => "$nom — $legende",
                    'numOrdre' => $ordre, 'idProduit' => $id,
                ]);
            }
        }

        return $ids;
    }

    private function creerCompatibilites(array $produits): void
    {
        $paires = [
            ['RBX-UNI-G1', 'RBX-UNI-R1'], ['RBX-FIG-02', 'RBX-FIG-03'],
            ['RBX-1X-NEO', 'RBX-1X-EVE'], ['RBX-POL-R2', 'RBX-POL-MINI'],
        ];

        foreach ($paires as [$a, $b]) {
            // Contrainte CK_Compatibilite_ordre : idProduit1 < idProduit2
            $this->db->table('CompatibiliteProduit')->insert([
                'idProduit1' => min($produits[$a], $produits[$b]),
                'idProduit2' => max($produits[$a], $produits[$b]),
            ]);
        }
    }

    /** @return array<string, int> ville => idShowroom */
    private function creerShowrooms(): array
    {
        $showrooms = [
            ['Robotix Paris Opéra', '12 boulevard Haussmann', '75009', 'Paris', 48.873400, 2.333500, '01 42 00 19 19'],
            ['Robotix Lyon Part-Dieu', '17 rue du Docteur Bouchut', '69003', 'Lyon', 45.761200, 4.856200, '04 72 00 19 19'],
            ['Robotix Marseille Vieux-Port', '1 quai du Port', '13002', 'Marseille', 43.296500, 5.369800, '04 91 00 19 19'],
        ];
        $ids = [];

        foreach ($showrooms as [$nom, $adresse, $cp, $ville, $lat, $lng, $tel]) {
            $this->db->table('Showroom')->insert([
                'nom' => $nom, 'adresse' => $adresse, 'codePostal' => $cp, 'ville' => $ville,
                'latitude' => $lat, 'longitude' => $lng, 'telephone' => $tel,
                'horaires' => 'Du mardi au samedi, 10 h – 19 h',
            ]);
            $ids[$ville] = (int) $this->db->insertID();
        }

        return $ids;
    }

    private function creerEvenements(array $showrooms, array $produits): void
    {
        // [titre, type, début, fin, ville du showroom ou null, référence produit ou null, description]
        $evenements = [
            ['Démonstration Unitree G1', 'demo', '2026-10-08T14:00:00', '2026-10-08T17:00:00', 'Paris', 'RBX-UNI-G1', 'Venez voir le G1 marcher, saluer et jouer au ballon.'],
            ['Lancement de Figure 03 en France', 'lancement', '2026-10-15T10:00:00', '2026-10-15T18:00:00', 'Lyon', 'RBX-FIG-03', 'Présentation officielle et premières précommandes.'],
            ['Atelier programmation Reachy Mini', 'atelier', '2026-10-21T18:30:00', '2026-10-21T20:30:00', 'Marseille', 'RBX-POL-MINI', 'Initiation à la programmation Python, dès 10 ans (12 places).'],
            ['Salon Innorobo — Lyon Eurexpo', 'salon', '2026-10-24T09:00:00', '2026-10-25T19:00:00', null, null, 'Retrouvez Robotix sur le stand B12 pendant deux jours.'],
            ['Démonstration NEO Gamma', 'demo', '2026-11-05T14:00:00', '2026-11-05T17:00:00', 'Lyon', 'RBX-1X-NEO', 'NEO Gamma en situation dans un salon reconstitué.'],
            ['Atelier : apprendre un geste à Reachy 2', 'atelier', '2026-11-12T18:30:00', '2026-11-12T20:30:00', 'Paris', 'RBX-POL-R2', 'Apprentissage par démonstration avec les bras de Reachy 2.'],
            ['Journée Unitree H1', 'lancement', '2026-11-19T10:00:00', '2026-11-19T18:00:00', 'Paris', 'RBX-UNI-H1', 'Essais et rencontre avec un ingénieur Unitree.'],
            ['Démonstration Digit', 'demo', '2026-11-25T14:00:00', '2026-11-25T17:00:00', 'Marseille', 'RBX-AGI-DGT', 'Digit porte vos courses : démonstration de charge.'],
            ['Démonstration EVE', 'demo', '2026-12-03T14:00:00', '2026-12-03T17:00:00', 'Paris', 'RBX-1X-EVE', 'Découvrez le robot de présence EVE.'],
            ['Atelier famille Unitree R1', 'atelier', '2026-12-09T18:30:00', '2026-12-09T20:30:00', 'Lyon', 'RBX-UNI-R1', 'Programmer une danse de Noël en famille.'],
            ['Marché de Noël des robots', 'salon', '2026-12-12T10:00:00', '2026-12-12T18:00:00', 'Marseille', null, 'Toute la gamme exposée, offres spéciales.'],
            ['Démonstration Figure 02', 'demo', '2026-12-17T14:00:00', '2026-12-17T17:00:00', 'Lyon', 'RBX-FIG-02', 'Figure 02 range une cuisine en direct.'],
        ];

        foreach ($evenements as [$titre, $type, $debut, $fin, $ville, $reference, $description]) {
            $this->db->table('Evenement')->insert([
                'titre' => $titre, 'type' => $type, 'description' => $description,
                'dateDebut' => $debut, 'dateFin' => $fin,
                'idShowroom' => $ville === null ? null : $showrooms[$ville],
                'idProduit' => $reference === null ? null : $produits[$reference],
            ]);
        }
    }

    private function creerComptes(): void
    {
        $client = $this->creerUtilisateur('Martin', 'Camille', 'client@robotix.test');
        $this->db->table('Client')->insert(['idUtilisateur' => $client, 'telephone' => '06 12 34 56 78']);
        $this->db->table('Adresse')->insert([
            'libelle' => 'Domicile', 'ligne1' => '10 rue de la République', 'codePostal' => '69002',
            'ville' => 'Lyon', 'pays' => 'France', 'idUtilisateur' => $client,
        ]);

        $redacteur = $this->creerUtilisateur('Dubois', 'Léa', 'redacteur@robotix.test');
        $this->db->table('Redacteur')->insert([
            'idUtilisateur' => $redacteur, 'pseudo' => 'LeaTech',
            'biographie' => 'Journaliste tech passionnée par la robotique humanoïde.',
        ]);

        $admin = $this->creerUtilisateur('Bernard', 'Hugo', 'admin@robotix.test');
        $this->db->table('Administrateur')
            ->set('dateNomination', 'CAST(GETDATE() AS DATE)', false)
            ->insert(['idUtilisateur' => $admin]);
    }

    private function creerUtilisateur(string $nom, string $prenom, string $email): int
    {
        $this->db->table('Utilisateur')->insert([
            'nom' => $nom, 'prenom' => $prenom, 'email' => $email,
            'motDePasse' => password_hash(self::MOT_DE_PASSE, PASSWORD_DEFAULT), 'actif' => 1,
        ]);

        return (int) $this->db->insertID();
    }
}
```

- [ ] **Step 8 : Lancer le test pour vérifier qu'il passe**

Run : `php vendor/bin/phpunit tests/database/SeederTest.php`
Expected : PASS (5 tests).

- [ ] **Step 9 : Remplir réellement la base**

Run : `php spark db:seed RobotixSeeder`
Expected : « Seeded: App\Database\Seeds\RobotixSeeder ». Les tests suivants s'appuient sur ces données.

- [ ] **Step 10 : Point de contrôle** — `php vendor/bin/phpunit` : tout est vert.

---

### Task 4 : Gabarit responsive, menu, CSS personnel et page News

**Files :**
- Modify : `app/Config/Routes.php` (remplacé entièrement)
- Delete : `app/Controllers/Home.php`, `app/Views/welcome_message.php`
- Create : `app/Controllers/Pages.php`
- Create : `app/Views/layouts/main.php`
- Create : `app/Views/partials/header.php`, `partials/footer.php`, `partials/flash.php`
- Create : `app/Views/pages/accueil.php`, `app/Views/pages/news.php`
- Create : `public/css/robotix.css`, `public/js/main.js`
- Create : `public/images/ui/robot-anatomie.svg` (visuel du bandeau, réutilisé par l'image réactive en tâche 5)
- Test : `tests/feature/NavigationTest.php`

**Interfaces :**
- Consumes : `menu_actif()` (tâche 1).
- Produces : le layout `layouts/main.php`, avec les sections `contenu` et `scripts` et les variables de vue `titre` et `description`. Les vues l'utilisent ainsi : `<?= $this->extend('layouts/main') ?><?= $this->section('contenu') ?>…<?= $this->endSection() ?>`.
- Produces : les classes CSS `section`, `section--sombre`, `titre-section`, `en-tete-page`, `btn-robotix`, `btn-contour` et `univers`.
- Produces : dans `main.js`, la confirmation des formulaires portant `data-confirm="…"`.

- [ ] **Step 1 : Écrire le test de navigation (échoue)**

`tests/feature/NavigationTest.php` :

```php
<?php

use Tests\Support\RobotixTestCase;

final class NavigationTest extends RobotixTestCase
{
    public function testAccueilUtiliseLeGabarit(): void
    {
        $resultat = $this->get('/');

        $resultat->assertOK();
        $resultat->assertSee('ROBOTIX');
        $resultat->assertSee('css/robotix.css');
        $resultat->assertSee('name="viewport"');
        $resultat->assertSee('Notre activité', 'h2');
    }

    public function testLeMenuContientToutesLesRubriques(): void
    {
        $resultat = $this->get('/');

        foreach (['Accueil', 'Store', 'News', 'Tarifs', 'Événements', 'Showrooms', 'Contact', 'Compte'] as $rubrique) {
            $resultat->assertSee($rubrique);
        }
    }

    public function testLaRubriqueCouranteEstMiseEnEvidence(): void
    {
        $corps = $this->get('news')->getBody();

        $this->assertMatchesRegularExpression('#class="nav-link active" aria-current="page" href="[^"]*/news"#', $corps);
        $this->assertSame(1, substr_count($corps, 'aria-current="page"'));
    }

    public function testPageNewsEnPreparation(): void
    {
        $this->get('news')->assertSee('Robotix News', 'h1');
    }
}
```

- [ ] **Step 2 : Lancer le test pour vérifier qu'il échoue**

Run : `php vendor/bin/phpunit tests/feature/NavigationTest.php`
Expected : FAIL (la page d'accueil CodeIgniter ne contient pas « ROBOTIX »).

- [ ] **Step 3 : Routes et contrôleur**

`app/Config/Routes.php` :

```php
<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Pages vitrine
$routes->get('/', 'Pages::accueil');
$routes->get('news', 'Pages::news');
```

Supprimer l'exemple d'origine :

```powershell
Remove-Item app\Controllers\Home.php, app\Views\welcome_message.php
```

`app/Controllers/Pages.php` :

```php
<?php

namespace App\Controllers;

/**
 * Pages vitrine de Robotix.
 */
class Pages extends BaseController
{
    public function accueil(): string
    {
        return view('pages/accueil', [
            'titre'       => 'Accueil',
            'description' => 'Robotix : robots humanoïdes pour les particuliers, actualités et démonstrations.',
        ]);
    }

    public function news(): string
    {
        return view('pages/news', ['titre' => 'Robotix News']);
    }
}
```

- [ ] **Step 4 : Layout et partials**

`app/Views/layouts/main.php` :

```php
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= esc($description ?? 'Robotix : robots humanoïdes pour les particuliers.') ?>">
    <title><?= esc($titre ?? 'Robotix') ?> — Robotix</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;family=Orbitron:wght@600;800&amp;display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url('css/robotix.css') ?>">
</head>
<body>
    <a class="visually-hidden-focusable lien-evitement" href="#contenu">Aller au contenu</a>
    <?= $this->include('partials/header') ?>
    <main id="contenu">
        <?= $this->include('partials/flash') ?>
        <?= $this->renderSection('contenu') ?>
    </main>
    <?= $this->include('partials/footer') ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/main.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
```

`app/Views/partials/header.php` :

```php
<?php
$rubriques = [
    ''           => 'Accueil',
    'store'      => 'Store',
    'news'       => 'News',
    'tarifs'     => 'Tarifs',
    'evenements' => 'Événements',
    'showrooms'  => 'Showrooms',
    'contact'    => 'Contact',
];
$connecte = (bool) session('idUtilisateur');
?>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-nuit fixed-top" aria-label="Navigation principale">
        <div class="container">
            <a class="navbar-brand logo" href="<?= site_url('/') ?>">ROBOTIX<span>.</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"
                    aria-controls="menu" aria-expanded="false" aria-label="Ouvrir le menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <?php foreach ($rubriques as $segment => $libelle): ?>
                        <?php $actif = menu_actif($segment); ?>
                        <li class="nav-item">
                            <a class="nav-link<?= $actif ? ' active' : '' ?>"<?= $actif ? ' aria-current="page"' : '' ?> href="<?= site_url($segment) ?>"><?= $libelle ?></a>
                        </li>
                    <?php endforeach ?>
                    <li class="nav-item dropdown">
                        <button class="nav-link dropdown-toggle btn btn-link<?= menu_actif('compte') || menu_actif('admin') ? ' active' : '' ?>"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= $connecte ? esc(session('prenom')) : 'Compte' ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ($connecte): ?>
                                <?php if (session('role') === 'admin'): ?>
                                    <li><a class="dropdown-item" href="<?= site_url('admin/evenements') ?>">Gérer le planning</a></li>
                                <?php endif ?>
                                <li>
                                    <form action="<?= site_url('compte/deconnexion') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <button class="dropdown-item" type="submit">Déconnexion</button>
                                    </form>
                                </li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="<?= site_url('compte/connexion') ?>">Connexion</a></li>
                                <li><a class="dropdown-item" href="<?= site_url('compte/inscription') ?>">Créer un compte</a></li>
                            <?php endif ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
```

`app/Views/partials/flash.php` :

```php
<?php foreach (['succes' => 'success', 'erreur' => 'danger'] as $cle => $classe): ?>
    <?php if ($message = session()->getFlashdata($cle)): ?>
        <div class="container mt-3">
            <div class="alert alert-<?= $classe ?> alert-dismissible fade show" role="alert">
                <?= esc($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        </div>
    <?php endif ?>
<?php endforeach ?>
```

`app/Views/partials/footer.php` :

```php
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <p class="logo mb-2">ROBOTIX<span>.</span></p>
                <p>Robots humanoïdes pour les particuliers : vente, conseils et démonstrations dans nos showrooms.</p>
            </div>
            <div class="col-6 col-md-4">
                <h2 class="h6 text-white">Navigation</h2>
                <ul class="list-unstyled">
                    <li><a href="<?= site_url('store') ?>">Robotix Store</a></li>
                    <li><a href="<?= site_url('news') ?>">Robotix News</a></li>
                    <li><a href="<?= site_url('tarifs') ?>">Tarifs</a></li>
                    <li><a href="<?= site_url('evenements') ?>">Événements</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-4">
                <h2 class="h6 text-white">Nous trouver</h2>
                <ul class="list-unstyled">
                    <li><a href="<?= site_url('showrooms') ?>">Nos showrooms</a></li>
                    <li><a href="<?= site_url('contact') ?>">Contact</a></li>
                </ul>
            </div>
        </div>
        <p class="site-footer__mentions">
            © <?= date('Y') ?> Robotix — site fictif réalisé dans le cadre du BTS SIO (AP1) par Ihab Benmahrouz.
            Les prix sont donnés à titre d'exemple.
        </p>
    </div>
</footer>
```

- [ ] **Step 5 : Vues accueil (version 1) et News**

`app/Views/pages/accueil.php` :

```php
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <p class="hero__surtitre">Robots humanoïdes pour particuliers</p>
                <h1>Le robot qui vous aide <em>à la maison</em> est enfin là.</h1>
                <p>Robotix sélectionne, vend et installe chez vous les meilleurs robots humanoïdes du marché,
                   et vous tient informé de toute leur actualité.</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a class="btn btn-robotix" href="<?= site_url('store') ?>">Découvrir le Store</a>
                    <a class="btn btn-contour" href="<?= site_url('tarifs') ?>">Calculer mon prix</a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <img class="img-fluid hero__visuel" src="<?= base_url('images/ui/robot-anatomie.svg') ?>"
                     alt="Illustration d'un robot humanoïde Robotix" width="600" height="800">
            </div>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="titre-activite">
    <div class="container">
        <h2 id="titre-activite" class="titre-section">Notre activité</h2>
        <p class="text-center text-secondary mb-5">Deux univers complémentaires et un accompagnement de A à Z.</p>
        <div class="row g-4">
            <div class="col-md-4">
                <article class="univers">
                    <p class="univers__icone" aria-hidden="true">🤖</p>
                    <h3 class="h5">Robotix Store</h3>
                    <p>Une sélection de robots humanoïdes compagnons, domestiques, éducatifs et premium, livrés et installés chez vous.</p>
                    <a href="<?= site_url('store') ?>">Voir le catalogue →</a>
                </article>
            </div>
            <div class="col-md-4">
                <article class="univers univers--violet">
                    <p class="univers__icone" aria-hidden="true">📰</p>
                    <h3 class="h5">Robotix News</h3>
                    <p>Tests, comparatifs et nouveautés : toute l'actualité des robots vendus sur le Store.</p>
                    <a href="<?= site_url('news') ?>">Lire les articles →</a>
                </article>
            </div>
            <div class="col-md-4">
                <article class="univers univers--ambre">
                    <p class="univers__icone" aria-hidden="true">🛠️</p>
                    <h3 class="h5">Services et showrooms</h3>
                    <p>Démonstrations gratuites, ateliers, financement et maintenance dans nos showrooms de Paris, Lyon et Marseille.</p>
                    <a href="<?= site_url('evenements') ?>">Voir les événements →</a>
                </article>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
```

`app/Views/pages/news.php` :

```php
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Robotix News</h1>
        <p>L'actualité des robots humanoïdes vendus sur Robotix Store.</p>
    </div>
</section>

<section class="section">
    <div class="container text-center">
        <p class="lead">Nos rédacteurs préparent les premiers articles : tests, comparatifs et coulisses des lancements.</p>
        <p>En attendant, retrouvez les robots en démonstration lors de nos prochains événements.</p>
        <a class="btn btn-robotix" href="<?= site_url('evenements') ?>">Voir le calendrier</a>
    </div>
</section>

<?= $this->endSection() ?>
```

- [ ] **Step 6 : Visuel du robot (SVG 600×800)**

`public/images/ui/robot-anatomie.svg`. Les zones sont dessinées pour correspondre aux coordonnées de l'image réactive : tête 230,40,370,180 ; torse 200,190,400,450 ; main gauche 100,460,200,550 ; main droite 400,460,500,550 ; jambes 210,460,390,780.

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 800" width="600" height="800">
  <defs>
    <linearGradient id="metal" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#f1f5f9"/><stop offset="1" stop-color="#94a3b8"/>
    </linearGradient>
    <linearGradient id="accent" x1="0" y1="0" x2="1" y2="0">
      <stop offset="0" stop-color="#00d4ff"/><stop offset="1" stop-color="#7c3aed"/>
    </linearGradient>
  </defs>
  <!-- Tête -->
  <rect x="240" y="50" width="120" height="120" rx="50" fill="url(#metal)"/>
  <rect x="258" y="88" width="84" height="38" rx="19" fill="#0b1020"/>
  <circle cx="282" cy="107" r="9" fill="#00d4ff"/>
  <circle cx="318" cy="107" r="9" fill="#00d4ff"/>
  <rect x="285" y="170" width="30" height="22" fill="#64748b"/>
  <!-- Torse -->
  <rect x="205" y="192" width="190" height="255" rx="40" fill="url(#metal)"/>
  <rect x="245" y="230" width="110" height="70" rx="14" fill="#0b1020"/>
  <rect x="258" y="250" width="84" height="10" rx="5" fill="url(#accent)"/>
  <rect x="258" y="270" width="56" height="10" rx="5" fill="#00d4ff" opacity=".6"/>
  <circle cx="300" cy="370" r="26" fill="url(#accent)"/>
  <!-- Bras -->
  <rect x="140" y="200" width="55" height="260" rx="27" fill="url(#metal)"/>
  <rect x="405" y="200" width="55" height="260" rx="27" fill="url(#metal)"/>
  <!-- Mains -->
  <circle cx="150" cy="505" r="40" fill="url(#accent)"/>
  <circle cx="450" cy="505" r="40" fill="url(#accent)"/>
  <!-- Jambes -->
  <rect x="220" y="465" width="70" height="285" rx="30" fill="url(#metal)"/>
  <rect x="310" y="465" width="70" height="285" rx="30" fill="url(#metal)"/>
  <rect x="210" y="745" width="90" height="28" rx="14" fill="url(#accent)"/>
  <rect x="300" y="745" width="90" height="28" rx="14" fill="url(#accent)"/>
</svg>
```

- [ ] **Step 7 : CSS personnel**

`public/css/robotix.css` :

```css
/* ==========================================================================
   Robotix — feuille de style personnelle (surcharge Bootstrap 5)
   ========================================================================== */

:root {
    --rbx-nuit: #0b1020;
    --rbx-nuit-2: #141b33;
    --rbx-cyan: #00d4ff;
    --rbx-violet: #7c3aed;
    --rbx-ambre: #f59e0b;
    --rbx-vert: #10b981;
    --rbx-clair: #f5f7fb;
    --rbx-texte: #1e293b;
    --rbx-gris: #64748b;
    --rbx-rayon: 1rem;
    --rbx-ombre: 0 10px 30px rgba(11, 16, 32, .12);
    --rbx-titres: 'Orbitron', system-ui, sans-serif;
    --rbx-police: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
}

body {
    font-family: var(--rbx-police);
    color: var(--rbx-texte);
    background: var(--rbx-clair);
    padding-top: 66px; /* hauteur de la barre de navigation fixe */
}

h1, h2, h3, .logo { font-family: var(--rbx-titres); letter-spacing: .02em; }
a { color: var(--rbx-violet); }

.lien-evitement {
    position: absolute; top: .5rem; left: .5rem; z-index: 2000;
    background: #fff; padding: .5rem 1rem; border-radius: .5rem;
}

/* ---------- Navigation ---------- */
.bg-nuit { background: rgba(11, 16, 32, .96); transition: box-shadow .3s; }
.navbar.defile { box-shadow: 0 4px 20px rgba(0, 0, 0, .35); }
.logo { font-weight: 800; font-size: 1.35rem; color: #fff; }
.logo span { color: var(--rbx-cyan); }
.navbar .nav-link { color: #cbd5e1; font-weight: 600; position: relative; }
.navbar .nav-link:hover, .navbar .nav-link:focus { color: #fff; }
.navbar .nav-link.active { color: var(--rbx-cyan); }
.navbar .btn-link { text-decoration: none; }

@media (min-width: 992px) {
    .navbar .nav-link.active::after {
        content: '';
        position: absolute; left: .5rem; right: .5rem; bottom: .15rem;
        height: 2px; border-radius: 2px; background: var(--rbx-cyan);
    }
}

/* ---------- Boutons ---------- */
.btn-robotix {
    background: linear-gradient(135deg, var(--rbx-cyan), var(--rbx-violet));
    color: #fff; border: 0; border-radius: 999px;
    padding: .7rem 1.6rem; font-weight: 600;
    transition: transform .2s, box-shadow .2s;
}
.btn-robotix:hover, .btn-robotix:focus {
    color: #fff; transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 212, 255, .35);
}
.btn-contour {
    border: 2px solid var(--rbx-cyan); color: var(--rbx-cyan); background: transparent;
    border-radius: 999px; padding: .6rem 1.5rem; font-weight: 600;
}
.btn-contour:hover, .btn-contour:focus { background: var(--rbx-cyan); color: var(--rbx-nuit); }

/* ---------- Sections ---------- */
.section { padding: 4.5rem 0; }
.section--sombre { background: var(--rbx-nuit); color: #e2e8f0; }
.titre-section { text-align: center; font-size: clamp(1.5rem, 3vw, 2.2rem); margin-bottom: 1rem; }
.titre-section::after {
    content: ''; display: block; width: 64px; height: 4px; margin: .8rem auto 0;
    border-radius: 4px; background: linear-gradient(90deg, var(--rbx-cyan), var(--rbx-violet));
}
.en-tete-page { background: var(--rbx-nuit); color: #fff; padding: 3rem 0 2.5rem; }
.en-tete-page h1 { font-size: clamp(1.7rem, 4vw, 2.6rem); }
.en-tete-page p { color: #cbd5e1; margin: 0; }

/* ---------- Bandeau d'accueil ---------- */
.hero {
    color: #fff; padding: 4.5rem 0 4rem; overflow: hidden;
    background:
        radial-gradient(circle at 80% 20%, rgba(124, 58, 237, .45), transparent 50%),
        radial-gradient(circle at 10% 90%, rgba(0, 212, 255, .30), transparent 45%),
        var(--rbx-nuit);
}
.hero__surtitre { color: var(--rbx-cyan); text-transform: uppercase; letter-spacing: .15em; font-weight: 700; font-size: .85rem; }
.hero h1 { font-size: clamp(1.9rem, 5vw, 3.3rem); font-weight: 800; }
.hero h1 em { font-style: normal; color: var(--rbx-cyan); }
.hero p { color: #cbd5e1; font-size: 1.1rem; max-width: 36rem; }
.hero__visuel {
    max-height: 420px; width: auto;
    animation: flotter 5s ease-in-out infinite;
    filter: drop-shadow(0 20px 40px rgba(0, 212, 255, .25));
}
@keyframes flotter {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-14px); }
}

/* ---------- Univers ---------- */
.univers {
    background: #fff; border-radius: var(--rbx-rayon); padding: 2rem; height: 100%;
    box-shadow: var(--rbx-ombre); border-top: 4px solid var(--rbx-cyan);
    transition: transform .25s;
}
.univers:hover { transform: translateY(-6px); }
.univers--violet { border-top-color: var(--rbx-violet); }
.univers--ambre { border-top-color: var(--rbx-ambre); }
.univers__icone { font-size: 2.2rem; margin-bottom: .5rem; }

/* ---------- Pied de page ---------- */
.site-footer { background: var(--rbx-nuit); color: #94a3b8; padding: 3rem 0 1.5rem; }
.site-footer a { color: #cbd5e1; text-decoration: none; }
.site-footer a:hover, .site-footer a:focus { color: var(--rbx-cyan); }
.site-footer__mentions { border-top: 1px solid #1e293b; margin: 2rem 0 0; padding-top: 1.2rem; font-size: .85rem; }

/* ---------- Accessibilité ---------- */
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation: none !important; transition: none !important; }
}
```

- [ ] **Step 8 : JavaScript commun**

`public/js/main.js` :

```js
/* Robotix — comportements communs à toutes les pages */
(function () {
    'use strict';

    // Ombre sous la barre de navigation dès que l'on fait défiler la page
    const barre = document.querySelector('.navbar');
    const majOmbre = () => barre && barre.classList.toggle('defile', window.scrollY > 20);
    window.addEventListener('scroll', majOmbre, { passive: true });
    majOmbre();

    // Sur mobile, refermer le menu après le choix d'une rubrique
    document.querySelectorAll('#menu .nav-link[href]').forEach((lien) => {
        lien.addEventListener('click', () => {
            const menu = document.getElementById('menu');
            if (menu.classList.contains('show')) {
                bootstrap.Collapse.getOrCreateInstance(menu).hide();
            }
        });
    });

    // Confirmation des actions sensibles : <form data-confirm="Message ?">
    document.addEventListener('submit', (evenement) => {
        const message = evenement.target.dataset.confirm;
        if (message && !window.confirm(message)) {
            evenement.preventDefault();
        }
    });
})();
```

- [ ] **Step 9 : Lancer le test pour vérifier qu'il passe**

Run : `php vendor/bin/phpunit tests/feature/NavigationTest.php`
Expected : PASS (4 tests).

- [ ] **Step 10 : Vérification dans le navigateur**

Ouvrir `http://localhost/Robotix19/`. Dans DevTools (Ctrl+Maj+M), tester 375 px, 768 px et 1280 px : menu burger sous 992 px, aucun défilement horizontal, bandeau lisible.

- [ ] **Step 11 : Point de contrôle** — `php vendor/bin/phpunit` : tout est vert.

---

### Task 5 : Accueil complet et image réactive à zones cliquables

**Files :**
- Create : `app/Models/ProduitModel.php`, `app/Models/EvenementModel.php`, `app/Models/ShowroomModel.php`
- Create : `app/Libraries/AnatomieRobot.php`
- Create : `app/Views/partials/carte_robot.php`, `app/Views/partials/anatomie.php`
- Create : `public/js/image-map.js`
- Modify : `app/Controllers/Pages.php` (méthode `accueil`)
- Modify : `app/Views/pages/accueil.php` (ajout de sections)
- Modify : `public/css/robotix.css` (ajout en fin de fichier)
- Test : `tests/feature/AccueilTest.php`, `tests/js/image-map.test.js`

**Interfaces :**
- Produces : `ProduitModel::phares(int $n = 4): array`, `catalogue(array $filtres): array`, `fiche(int $id): ?array`, `compatibles(int $id): array`, `gammes(): array`, `pourCalculateur(): array`. Chaque ligne « robot » contient `idProduit, reference, nom, description, prixHt, tauxTva, stock, dateAjout, idCategorie, idMarque, marque, categorie, image, image2`.
- Produces : `EvenementModel::TYPES` (`['demo' => 'Démonstration', 'lancement' => 'Lancement', 'salon' => 'Salon', 'atelier' => 'Atelier']`), `prochains(int $n = 3): array`, `duMois(string $mois): array`, `tous(): array`, `chevauche(?int $idShowroom, string $debut, string $fin, ?int $exclure = null): bool`. Les lignes avec détails ajoutent `showroom`, `ville` et `produit`.
- Produces : `ShowroomModel` (table `Showroom`).
- Produces : `AnatomieRobot::zones(): array` (clé ⇒ `titre`, `coords`, `resume`, `detail`).
- Produces : le partial `partials/carte_robot` (variable `$robot`) et le partial `partials/anatomie` (sans variable). Une page qui inclut l'anatomie ajoute `<script src="<?= base_url('js/image-map.js') ?>"></script>` dans sa section `scripts`.
- Produces : `ImageMap.mettreAEchelle(coords: string, ratio: number): string` dans `image-map.js`.

- [ ] **Step 1 : Écrire les tests (échouent)**

`tests/feature/AccueilTest.php` :

```php
<?php

use Tests\Support\RobotixTestCase;

final class AccueilTest extends RobotixTestCase
{
    public function testLesRobotsPharesSontLesPlusHautDeGamme(): void
    {
        $resultat = $this->get('/');

        $resultat->assertSee('Robots phares', 'h2');
        $resultat->assertSee('Unitree H1');
        $resultat->assertSee('89 880,00 €'); // 74 900 € HT + 20 % de TVA
        $resultat->assertDontSee('Unitree R1'); // le moins cher n'est pas un robot phare
    }

    public function testImageReactiveAvecSesZones(): void
    {
        $resultat = $this->get('/');

        $resultat->assertSee('usemap="#carte-robot"');
        $resultat->assertSee('<map name="carte-robot">');
        $this->assertSame(5, substr_count($resultat->getBody(), '<area '));
        $resultat->assertSee('id="modale-zone"');
        $resultat->assertSee('js/image-map.js');
    }

    public function testSectionProchainsEvenements(): void
    {
        $this->get('/')->assertSee('Prochains événements', 'h2');
    }
}
```

`tests/js/image-map.test.js` :

```js
const test = require('node:test');
const assert = require('node:assert');
const { mettreAEchelle } = require('../../public/js/image-map.js');

test('réduit les coordonnées de moitié', () => {
    assert.strictEqual(mettreAEchelle('100,200,300,400', 0.5), '50,100,150,200');
});

test('arrondit au pixel le plus proche', () => {
    assert.strictEqual(mettreAEchelle('230,40,370,180', 0.55), '127,22,204,99');
});

test('ratio 1 : coordonnées inchangées', () => {
    assert.strictEqual(mettreAEchelle('210,460,390,780', 1), '210,460,390,780');
});
```

- [ ] **Step 2 : Lancer les tests pour vérifier qu'ils échouent**

Run : `php vendor/bin/phpunit tests/feature/AccueilTest.php` puis `node --test tests/js/`
Expected : FAIL (« Robots phares » absent ; « Cannot find module image-map.js »).

- [ ] **Step 3 : Modèles**

`app/Models/ProduitModel.php` :

```php
<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class ProduitModel extends Model
{
    protected $table         = 'Produit';
    protected $primaryKey    = 'idProduit';
    protected $returnType    = 'array';
    protected $allowedFields = ['reference', 'nom', 'description', 'prixHt', 'stock', 'actif', 'tauxTva', 'idCategorie', 'idMarque'];

    /** Robots actifs avec marque, catégorie et deux premières images. */
    private function base(): BaseBuilder
    {
        return $this->db->table('Produit p')
            ->select('p.idProduit, p.reference, p.nom, p.description, p.prixHt, p.tauxTva, p.stock, p.dateAjout,
                      p.idCategorie, p.idMarque, m.nom AS marque, c.libelle AS categorie,
                      i1.fichier AS image, i2.fichier AS image2')
            ->join('Marque m', 'm.idMarque = p.idMarque')
            ->join('Categorie c', 'c.idCategorie = p.idCategorie')
            ->join('Image i1', 'i1.idProduit = p.idProduit AND i1.numOrdre = 1', 'left')
            ->join('Image i2', 'i2.idProduit = p.idProduit AND i2.numOrdre = 2', 'left')
            ->where('p.actif', 1);
    }

    public function phares(int $n = 4): array
    {
        return $this->base()->orderBy('p.prixHt', 'DESC')->limit($n)->get()->getResultArray();
    }

    /**
     * @param array{categorie?: int|string, marque?: int|string, prixMax?: int|string, q?: string, tri?: string} $filtres
     */
    public function catalogue(array $filtres): array
    {
        $requete = $this->base();

        if (! empty($filtres['categorie'])) {
            $requete->where('p.idCategorie', (int) $filtres['categorie']);
        }
        if (! empty($filtres['marque'])) {
            $requete->where('p.idMarque', (int) $filtres['marque']);
        }
        if (! empty($filtres['prixMax'])) {
            // Filtre sur le prix TTC ; la valeur est convertie en nombre, donc sans risque d'injection
            $requete->where('p.prixHt * (1 + p.tauxTva / 100) <= ' . (float) $filtres['prixMax'], null, false);
        }
        if (! empty($filtres['q'])) {
            $requete->like('p.nom', trim((string) $filtres['q']));
        }

        match ($filtres['tri'] ?? 'nouveautes') {
            'prix_asc'  => $requete->orderBy('p.prixHt', 'ASC'),
            'prix_desc' => $requete->orderBy('p.prixHt', 'DESC'),
            default     => $requete->orderBy('p.dateAjout', 'DESC')->orderBy('p.nom', 'ASC'),
        };

        return $requete->get()->getResultArray();
    }

    public function fiche(int $id): ?array
    {
        return $this->base()->where('p.idProduit', $id)->get()->getRowArray();
    }

    /** Robots déclarés compatibles (dans un sens ou dans l'autre). */
    public function compatibles(int $id): array
    {
        $paires = $this->db->table('CompatibiliteProduit')
            ->groupStart()->where('idProduit1', $id)->orWhere('idProduit2', $id)->groupEnd()
            ->get()->getResultArray();

        $autres = array_map(
            static fn (array $paire): int => (int) $paire['idProduit1'] === $id ? (int) $paire['idProduit2'] : (int) $paire['idProduit1'],
            $paires,
        );

        return $autres === [] ? [] : $this->base()->whereIn('p.idProduit', $autres)->get()->getResultArray();
    }

    /** Une ligne par catégorie : prix HT minimum et nombre de robots actifs. */
    public function gammes(): array
    {
        return $this->db->query(
            'SELECT c.idCategorie, c.libelle, c.description, g.prixMin, g.nbRobots
             FROM Categorie c
             JOIN (SELECT idCategorie, MIN(prixHt) AS prixMin, COUNT(*) AS nbRobots
                   FROM Produit WHERE actif = 1 GROUP BY idCategorie) g
               ON g.idCategorie = c.idCategorie
             ORDER BY g.prixMin'
        )->getResultArray();
    }

    /** Liste légère (id, nom, prix) pour le calculateur et les listes déroulantes. */
    public function pourCalculateur(): array
    {
        return $this->select('idProduit, nom, prixHt, tauxTva')
            ->where('actif', 1)->orderBy('nom')->findAll();
    }
}
```

`app/Models/EvenementModel.php` :

```php
<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class EvenementModel extends Model
{
    public const TYPES = [
        'demo'      => 'Démonstration',
        'lancement' => 'Lancement',
        'salon'     => 'Salon',
        'atelier'   => 'Atelier',
    ];

    protected $table         = 'Evenement';
    protected $primaryKey    = 'idEvenement';
    protected $returnType    = 'array';
    protected $allowedFields = ['titre', 'description', 'type', 'dateDebut', 'dateFin', 'idShowroom', 'idProduit'];

    private function avecDetails(): BaseBuilder
    {
        return $this->db->table('Evenement e')
            ->select('e.idEvenement, e.titre, e.description, e.type, e.dateDebut, e.dateFin, e.idShowroom, e.idProduit,
                      s.nom AS showroom, s.ville, p.nom AS produit')
            ->join('Showroom s', 's.idShowroom = e.idShowroom', 'left')
            ->join('Produit p', 'p.idProduit = e.idProduit', 'left');
    }

    public function prochains(int $n = 3): array
    {
        return $this->avecDetails()
            ->where('e.dateFin >= GETDATE()', null, false)
            ->orderBy('e.dateDebut', 'ASC')->limit($n)
            ->get()->getResultArray();
    }

    /** Événements qui touchent le mois « AAAA-MM » (y compris ceux à cheval sur deux mois). */
    public function duMois(string $mois): array
    {
        $debut = $mois . '-01T00:00:00';
        $fin   = date('Y-m-d\TH:i:s', strtotime($mois . '-01 +1 month'));

        return $this->avecDetails()
            ->where('e.dateDebut <', $fin)
            ->where('e.dateFin >=', $debut)
            ->orderBy('e.dateDebut', 'ASC')
            ->get()->getResultArray();
    }

    public function tous(): array
    {
        return $this->avecDetails()->orderBy('e.dateDebut', 'DESC')->get()->getResultArray();
    }

    /** Vrai si le showroom a déjà un événement qui recoupe [$debut ; $fin[. */
    public function chevauche(?int $idShowroom, string $debut, string $fin, ?int $exclure = null): bool
    {
        if ($idShowroom === null) {
            return false;
        }

        $requete = $this->db->table('Evenement')
            ->where('idShowroom', $idShowroom)
            ->where('dateDebut <', $fin)
            ->where('dateFin >', $debut);

        if ($exclure !== null) {
            $requete->where('idEvenement !=', $exclure);
        }

        return $requete->countAllResults() > 0;
    }
}
```

`app/Models/ShowroomModel.php` :

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class ShowroomModel extends Model
{
    protected $table         = 'Showroom';
    protected $primaryKey    = 'idShowroom';
    protected $returnType    = 'array';
    protected $allowedFields = ['nom', 'adresse', 'codePostal', 'ville', 'latitude', 'longitude', 'telephone', 'horaires', 'image'];
}
```

- [ ] **Step 4 : Contenu des zones de l'image réactive**

`app/Libraries/AnatomieRobot.php` :

```php
<?php

namespace App\Libraries;

/**
 * Zones cliquables de l'image réactive « robot-anatomie.svg » (600×800).
 * Coordonnées au format <area shape="rect"> : x1,y1,x2,y2.
 */
final class AnatomieRobot
{
    public static function zones(): array
    {
        return [
            'tete' => [
                'titre'  => 'Tête — vision et dialogue',
                'coords' => '230,40,370,180',
                'resume' => 'Caméras stéréo, micros et haut-parleur.',
                'detail' => 'Deux caméras mesurent la profondeur pour reconnaître les visages et les objets. Quatre micros repèrent d\'où vient la voix et le robot vous répond en français.',
            ],
            'torse' => [
                'titre'  => 'Torse — énergie et intelligence',
                'coords' => '200,190,400,450',
                'resume' => 'Batterie et ordinateur embarqué.',
                'detail' => 'Une batterie d\'environ 2 kWh offre près de 4 heures d\'autonomie, avec retour automatique à la station de charge. L\'ordinateur embarqué traite vos demandes sans envoyer vos données dans le cloud.',
            ],
            'main-gauche' => [
                'titre'  => 'Main gauche — préhension',
                'coords' => '100,460,200,550',
                'resume' => 'Saisir et porter des objets.',
                'detail' => 'Des doigts articulés et des capteurs de force permettent de saisir un verre sans le briser ou de porter un panier de linge.',
            ],
            'main-droite' => [
                'titre'  => 'Main droite — précision',
                'coords' => '400,460,500,550',
                'resume' => 'Gestes fins et outils.',
                'detail' => 'La main droite manipule les petits objets : boutons, poignées, couverts. Elle apprend de nouveaux gestes quand vous les lui montrez.',
            ],
            'jambes' => [
                'titre'  => 'Jambes — motricité',
                'coords' => '210,460,390,780',
                'resume' => 'Marche stable et escaliers.',
                'detail' => 'Des moteurs à couple élevé et une centrale inertielle assurent l\'équilibre : marche à 1,5 m/s, montée d\'escaliers et rattrapage en cas de bousculade.',
            ],
        ];
    }
}
```

- [ ] **Step 5 : Partials**

`app/Views/partials/carte_robot.php` :

```php
<?php
/** @var array $robot ligne issue de ProduitModel */
$lien = site_url('store/robot/' . $robot['idProduit']);
?>
<article class="carte-robot">
    <a class="carte-robot__visuel" href="<?= $lien ?>" tabindex="-1" aria-hidden="true">
        <img class="img-fluid carte-robot__img" src="<?= base_url('images/robots/' . ($robot['image'] ?? 'defaut.svg')) ?>"
             alt="" width="400" height="500" loading="lazy">
        <?php if (! empty($robot['image2'])): ?>
            <img class="img-fluid carte-robot__img carte-robot__img--survol" src="<?= base_url('images/robots/' . $robot['image2']) ?>"
                 alt="" width="400" height="500" loading="lazy">
        <?php endif ?>
    </a>
    <div class="carte-robot__corps">
        <p class="carte-robot__marque"><?= esc($robot['marque']) ?> · <?= esc($robot['categorie']) ?></p>
        <h3 class="carte-robot__nom"><a href="<?= $lien ?>"><?= esc($robot['nom']) ?></a></h3>
        <p class="carte-robot__prix"><?= euros(prix_ttc($robot['prixHt'], $robot['tauxTva'])) ?> <small>TTC</small></p>
    </div>
</article>
```

`app/Views/partials/anatomie.php` :

```php
<?php $zones = \App\Libraries\AnatomieRobot::zones(); ?>
<section class="section section--sombre anatomie" aria-labelledby="titre-anatomie">
    <div class="container">
        <h2 id="titre-anatomie" class="titre-section">Anatomie d'un robot Robotix</h2>
        <p class="text-center mb-5">Survolez une partie du robot pour un aperçu, cliquez pour tout savoir.</p>
        <div class="row align-items-center g-5">
            <div class="col-md-6">
                <div class="image-map" data-image-map>
                    <img class="img-fluid" src="<?= base_url('images/ui/robot-anatomie.svg') ?>" usemap="#carte-robot"
                         width="600" height="800" alt="Schéma d'un robot humanoïde : tête, torse, mains et jambes">
                    <map name="carte-robot">
                        <?php foreach ($zones as $cle => $zone): ?>
                            <area shape="rect" coords="<?= $zone['coords'] ?>" href="#zone-info"
                                  alt="<?= esc($zone['titre']) ?>" data-zone="<?= $cle ?>">
                        <?php endforeach ?>
                    </map>
                    <div class="image-map__surbrillance" hidden></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="image-map__info" id="zone-info" aria-live="polite">
                    <h3 class="h5">Choisissez une zone</h3>
                    <p class="mb-0">Tête, torse, mains ou jambes : chaque partie cache une technologie.</p>
                </div>
                <ul class="image-map__liste">
                    <?php foreach ($zones as $cle => $zone): ?>
                        <li><button type="button" class="btn btn-contour btn-sm" data-zone-bouton="<?= $cle ?>"><?= esc($zone['titre']) ?></button></li>
                    <?php endforeach ?>
                </ul>
            </div>
        </div>
    </div>
    <script type="application/json" id="zones-robot"><?= json_encode($zones, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?></script>
</section>

<div class="modal fade" id="modale-zone" tabindex="-1" aria-labelledby="modale-zone-titre" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title h5" id="modale-zone-titre">Zone du robot</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="modale-zone-texte"></div>
            <div class="modal-footer">
                <a class="btn btn-robotix" href="<?= site_url('store') ?>">Voir les robots</a>
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 6 : Script de l'image réactive**

`public/js/image-map.js` :

```js
/* Robotix — image réactive : zones <area> redimensionnées, survol (div) et clic (popup) */
(function (racine) {
    'use strict';

    /** « x1,y1,x2,y2 » multipliées par ratio, arrondies au pixel. */
    function mettreAEchelle(coords, ratio) {
        return coords.split(',').map((valeur) => Math.round(Number(valeur) * ratio)).join(',');
    }

    const api = { mettreAEchelle };
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
        return;
    }
    racine.ImageMap = api;

    function initialiser(bloc) {
        const image = bloc.querySelector('img[usemap]');
        const zonesHtml = bloc.querySelectorAll('area');
        const surbrillance = bloc.querySelector('.image-map__surbrillance');
        const info = document.getElementById('zone-info');
        const zones = JSON.parse(document.getElementById('zones-robot').textContent);
        const modale = new bootstrap.Modal(document.getElementById('modale-zone'));

        zonesHtml.forEach((zone) => { zone.dataset.coordsOrigine = zone.getAttribute('coords'); });

        // Les coordonnées <area> sont en pixels : on les recalcule selon la taille affichée
        function redimensionner() {
            const ratio = image.clientWidth / (image.naturalWidth || 600);
            zonesHtml.forEach((zone) => { zone.coords = mettreAEchelle(zone.dataset.coordsOrigine, ratio); });
        }

        function surligner(zone) {
            const [x1, y1, x2, y2] = zone.coords.split(',').map(Number);
            Object.assign(surbrillance.style, { left: x1 + 'px', top: y1 + 'px', width: (x2 - x1) + 'px', height: (y2 - y1) + 'px' });
            surbrillance.hidden = false;
        }

        function afficherApercu(cle) {
            const zone = zones[cle];
            const titre = document.createElement('h3');
            const texte = document.createElement('p');
            titre.className = 'h5';
            titre.textContent = zone.titre;
            texte.className = 'mb-0';
            texte.textContent = zone.resume + ' Cliquez pour en savoir plus.';
            info.replaceChildren(titre, texte);
        }

        function ouvrirModale(cle) {
            document.getElementById('modale-zone-titre').textContent = zones[cle].titre;
            document.getElementById('modale-zone-texte').textContent = zones[cle].detail;
            modale.show();
        }

        zonesHtml.forEach((zone) => {
            const entrer = () => { surligner(zone); afficherApercu(zone.dataset.zone); };
            zone.addEventListener('mouseenter', entrer);
            zone.addEventListener('focus', entrer);
            zone.addEventListener('mouseleave', () => { surbrillance.hidden = true; });
            zone.addEventListener('blur', () => { surbrillance.hidden = true; });
            zone.addEventListener('click', (evenement) => {
                evenement.preventDefault();
                ouvrirModale(zone.dataset.zone);
            });
        });

        // Boutons équivalents (accessibles au clavier et sur mobile)
        document.querySelectorAll('[data-zone-bouton]').forEach((bouton) => {
            bouton.addEventListener('click', () => ouvrirModale(bouton.dataset.zoneBouton));
        });

        window.addEventListener('resize', redimensionner);
        if (image.complete) {
            redimensionner();
        } else {
            image.addEventListener('load', redimensionner);
        }
    }

    document.querySelectorAll('[data-image-map]').forEach(initialiser);
})(this);
```

- [ ] **Step 7 : Contrôleur et vue d'accueil**

Dans `app/Controllers/Pages.php`, ajouter les `use` puis remplacer `accueil()` :

```php
use App\Models\EvenementModel;
use App\Models\ProduitModel;
```

```php
    public function accueil(): string
    {
        return view('pages/accueil', [
            'titre'       => 'Accueil',
            'description' => 'Robotix : robots humanoïdes pour les particuliers, actualités et démonstrations.',
            'phares'      => model(ProduitModel::class)->phares(4),
            'evenements'  => model(EvenementModel::class)->prochains(3),
            'types'       => EvenementModel::TYPES,
        ]);
    }
```

Dans `app/Views/pages/accueil.php`, insérer avant `<?= $this->endSection() ?>` :

```php
<section class="section bg-white" aria-labelledby="titre-phares">
    <div class="container">
        <h2 id="titre-phares" class="titre-section">Robots phares</h2>
        <p class="text-center text-secondary mb-5">Survolez un robot pour le voir en action.</p>
        <div class="row g-4">
            <?php foreach ($phares as $robot): ?>
                <div class="col-sm-6 col-lg-3"><?= view('partials/carte_robot', ['robot' => $robot]) ?></div>
            <?php endforeach ?>
        </div>
        <p class="text-center mt-5"><a class="btn btn-robotix" href="<?= site_url('store') ?>">Tout le catalogue</a></p>
    </div>
</section>

<?= $this->include('partials/anatomie') ?>

<section class="section" aria-labelledby="titre-evenements">
    <div class="container">
        <h2 id="titre-evenements" class="titre-section">Prochains événements</h2>
        <?php if ($evenements === []): ?>
            <p class="text-center">Aucun événement programmé pour le moment.</p>
        <?php else: ?>
            <div class="row g-4 mt-2">
                <?php foreach ($evenements as $evenement): ?>
                    <div class="col-md-4">
                        <article class="univers">
                            <p class="mb-2"><span class="pastille pastille--<?= esc($evenement['type']) ?>"></span><?= esc($types[$evenement['type']] ?? $evenement['type']) ?></p>
                            <h3 class="h6"><?= esc($evenement['titre']) ?></h3>
                            <p class="mb-0 text-secondary"><?= date_fr($evenement['dateDebut']) ?> — <?= esc($evenement['ville'] ?? 'Hors showroom') ?></p>
                        </article>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
        <p class="text-center mt-4"><a href="<?= site_url('evenements') ?>">Voir tout le calendrier →</a></p>
    </div>
</section>
```

Et à la toute fin du fichier (après le `endSection` du contenu) :

```php
<?= $this->section('scripts') ?>
<script src="<?= base_url('js/image-map.js') ?>"></script>
<?= $this->endSection() ?>
```

- [ ] **Step 8 : Styles (ajout à la fin de `robotix.css`)**

```css
/* ---------- Cartes robots (images réactives au survol) ---------- */
.carte-robot {
    background: #fff; border-radius: var(--rbx-rayon); overflow: hidden; height: 100%;
    box-shadow: var(--rbx-ombre); transition: transform .25s, box-shadow .25s;
}
.carte-robot:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(11, 16, 32, .2); }
.carte-robot__visuel { display: block; position: relative; overflow: hidden; aspect-ratio: 4 / 5; }
.carte-robot__img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s, opacity .4s; }
.carte-robot__img--survol { position: absolute; inset: 0; opacity: 0; }
.carte-robot:hover .carte-robot__img { transform: scale(1.06); }
.carte-robot:hover .carte-robot__img--survol { opacity: 1; }
.carte-robot__corps { padding: 1rem 1.2rem 1.3rem; }
.carte-robot__marque { color: var(--rbx-gris); font-size: .8rem; text-transform: uppercase; letter-spacing: .08em; margin-bottom: .3rem; }
.carte-robot__nom { font-size: 1.05rem; margin-bottom: .4rem; }
.carte-robot__nom a { color: var(--rbx-texte); text-decoration: none; }
.carte-robot__nom a::after { content: ''; position: absolute; inset: 0; } /* toute la carte est cliquable */
.carte-robot { position: relative; }
.carte-robot__prix { font-weight: 700; color: var(--rbx-violet); margin: 0; }

/* ---------- Image réactive (zones <area>) ---------- */
.image-map { position: relative; max-width: 420px; margin: 0 auto; }
.image-map img { display: block; width: 100%; height: auto; }
.image-map__surbrillance {
    position: absolute; pointer-events: none; border-radius: 1rem;
    border: 2px solid var(--rbx-cyan); background: rgba(0, 212, 255, .18);
    box-shadow: 0 0 24px rgba(0, 212, 255, .6);
}
.image-map__info {
    background: var(--rbx-nuit-2); border-left: 4px solid var(--rbx-cyan);
    border-radius: var(--rbx-rayon); padding: 1.5rem; min-height: 8rem;
}
.image-map__liste { list-style: none; padding: 0; margin: 1.5rem 0 0; display: flex; flex-wrap: wrap; gap: .5rem; }

/* ---------- Pastilles de type d'événement ---------- */
.pastille { display: inline-block; width: .7rem; height: .7rem; border-radius: 50%; margin-right: .4rem; vertical-align: middle; }
.pastille--demo { background: var(--rbx-cyan); }
.pastille--lancement { background: var(--rbx-violet); }
.pastille--salon { background: var(--rbx-ambre); }
.pastille--atelier { background: var(--rbx-vert); }
```

- [ ] **Step 9 : Lancer les tests pour vérifier qu'ils passent**

Run : `php vendor/bin/phpunit tests/feature/AccueilTest.php` puis `node --test tests/js/`
Expected : PASS (3 tests PHP, 3 tests JS).

- [ ] **Step 10 : Vérification dans le navigateur**

Sur `http://localhost/Robotix19/` : le survol de la tête du robot affiche un cadre lumineux et le texte à droite ; le clic ouvre la popup ; à 375 px, les zones restent alignées (redimensionnement) ; le survol d'une carte robot affiche la deuxième image.

- [ ] **Step 11 : Point de contrôle** — `php vendor/bin/phpunit` et `node --test tests/js/` : tout est vert.

---

### Task 6 : Robotix Store — catalogue, fiche robot et galerie en popup

**Files :**
- Create : `app/Models/CategorieModel.php`, `app/Models/MarqueModel.php`, `app/Models/ImageModel.php`
- Create : `app/Controllers/Store/Catalogue.php`, `app/Controllers/Store/Produit.php`
- Create : `app/Views/store/catalogue.php`, `app/Views/store/produit.php`
- Create : `public/js/galerie.js`
- Modify : `app/Config/Routes.php`, `public/css/robotix.css`
- Test : `tests/feature/StoreTest.php`, `tests/js/galerie.test.js`

**Interfaces :**
- Consumes : `ProduitModel::catalogue()`, `fiche()`, `compatibles()`, les partials `carte_robot` et `anatomie` (tâche 5).
- Produces : `ImageModel::duProduit(int $idProduit): array` (lignes `fichier`, `legende`, `numOrdre`) ; routes `store` et `store/robot/(:num)`.
- Produces : `Galerie.indexSuivant(i, n)` et `Galerie.indexPrecedent(i, n)` dans `galerie.js`.

- [ ] **Step 1 : Écrire les tests (échouent)**

`tests/feature/StoreTest.php` :

```php
<?php

use CodeIgniter\Exceptions\PageNotFoundException;
use Tests\Support\RobotixTestCase;

final class StoreTest extends RobotixTestCase
{
    public function testLeCatalogueAfficheLesDixRobots(): void
    {
        $resultat = $this->get('store');

        $resultat->assertOK();
        $resultat->assertSee('10 robots');
        $resultat->assertSee('Reachy Mini');
        $resultat->assertSee('Unitree H1');
    }

    public function testFiltreParCategorie(): void
    {
        $idEducatif = (int) $this->db->table('Categorie')->where('libelle', 'Éducatif')->get()->getRow('idCategorie');

        $resultat = $this->get('store', ['categorie' => $idEducatif]);

        $resultat->assertSee('3 robots');
        $resultat->assertSee('Reachy 2');
        $resultat->assertDontSee('Unitree H1');
    }

    public function testTriParPrixCroissant(): void
    {
        $corps = $this->get('store', ['tri' => 'prix_asc'])->getBody();

        $this->assertLessThan(strpos($corps, 'Unitree H1'), strpos($corps, 'Reachy Mini'));
    }

    public function testRechercheParNom(): void
    {
        $resultat = $this->get('store', ['q' => 'digit']);

        $resultat->assertSee('1 robot');
        $resultat->assertDontSee('NEO Gamma');
    }

    public function testFiltrePrixMaximumTtc(): void
    {
        $this->get('store', ['prixMax' => 6000])->assertSee('2 robots'); // R1 (5 880 €) et Reachy Mini
    }

    public function testFicheRobotAvecGalerieEtCompatibles(): void
    {
        $resultat = $this->get('store/robot/' . $this->idProduit('RBX-UNI-G1'));

        $resultat->assertOK();
        $resultat->assertSee('Unitree G1', 'h1');
        $resultat->assertSee('16 680,00 €');
        $resultat->assertSee('id="lightbox"');
        $resultat->assertSee('rbx-uni-g1-2.svg');
        $resultat->assertSee('Unitree R1'); // robot compatible
        $resultat->assertSee('usemap="#carte-robot"');
    }

    public function testRobotInexistantRenvoie404(): void
    {
        $this->assertPageIntrouvable('store/robot/999999');
    }

    public function testRobotInactifRenvoie404(): void
    {
        $id = $this->idProduit('RBX-UNI-G1');
        $this->db->table('Produit')->where('idProduit', $id)->update(['actif' => 0]);

        $this->assertPageIntrouvable('store/robot/' . $id);
    }

    private function assertPageIntrouvable(string $url): void
    {
        try {
            $this->get($url)->assertStatus(404);
        } catch (PageNotFoundException) {
            $this->addToAssertionCount(1);
        }
    }
}
```

`tests/js/galerie.test.js` :

```js
const test = require('node:test');
const assert = require('node:assert');
const { indexSuivant, indexPrecedent } = require('../../public/js/galerie.js');

test('passe à l\'image suivante puis revient au début', () => {
    assert.strictEqual(indexSuivant(0, 3), 1);
    assert.strictEqual(indexSuivant(2, 3), 0);
});

test('revient à la dernière image depuis la première', () => {
    assert.strictEqual(indexPrecedent(0, 3), 2);
    assert.strictEqual(indexPrecedent(1, 3), 0);
});
```

- [ ] **Step 2 : Lancer les tests pour vérifier qu'ils échouent**

Run : `php vendor/bin/phpunit tests/feature/StoreTest.php` puis `node --test tests/js/`
Expected : FAIL (route `store` inconnue ; module `galerie.js` introuvable).

- [ ] **Step 3 : Modèles simples**

`app/Models/CategorieModel.php` :

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class CategorieModel extends Model
{
    protected $table         = 'Categorie';
    protected $primaryKey    = 'idCategorie';
    protected $returnType    = 'array';
    protected $allowedFields = ['libelle', 'description'];
}
```

`app/Models/MarqueModel.php` :

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class MarqueModel extends Model
{
    protected $table         = 'Marque';
    protected $primaryKey    = 'idMarque';
    protected $returnType    = 'array';
    protected $allowedFields = ['nom', 'pays', 'siteWeb', 'logo', 'description', 'ticker', 'placeMarche'];
}
```

`app/Models/ImageModel.php` :

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class ImageModel extends Model
{
    protected $table         = 'Image';
    protected $primaryKey    = 'idImage';
    protected $returnType    = 'array';
    protected $allowedFields = ['fichier', 'legende', 'numOrdre', 'idProduit'];

    public function duProduit(int $idProduit): array
    {
        return $this->where('idProduit', $idProduit)->orderBy('numOrdre')->findAll();
    }
}
```

- [ ] **Step 4 : Routes**

Ajouter à `app/Config/Routes.php` :

```php
// Robotix Store
$routes->get('store', 'Store\Catalogue::index');
$routes->get('store/robot/(:num)', 'Store\Produit::show/$1');
```

- [ ] **Step 5 : Contrôleurs**

`app/Controllers/Store/Catalogue.php` :

```php
<?php

namespace App\Controllers\Store;

use App\Controllers\BaseController;
use App\Models\CategorieModel;
use App\Models\MarqueModel;
use App\Models\ProduitModel;

class Catalogue extends BaseController
{
    public function index(): string
    {
        $filtres = [
            'categorie' => (int) $this->request->getGet('categorie'),
            'marque'    => (int) $this->request->getGet('marque'),
            'prixMax'   => (int) $this->request->getGet('prixMax'),
            'q'         => trim((string) $this->request->getGet('q')),
            'tri'       => (string) ($this->request->getGet('tri') ?? 'nouveautes'),
        ];

        return view('store/catalogue', [
            'titre'      => 'Robotix Store',
            'robots'     => model(ProduitModel::class)->catalogue($filtres),
            'filtres'    => $filtres,
            'categories' => model(CategorieModel::class)->orderBy('libelle')->findAll(),
            'marques'    => model(MarqueModel::class)->orderBy('nom')->findAll(),
        ]);
    }
}
```

`app/Controllers/Store/Produit.php` :

```php
<?php

namespace App\Controllers\Store;

use App\Controllers\BaseController;
use App\Models\ImageModel;
use App\Models\ProduitModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Produit extends BaseController
{
    public function show(int $id): string
    {
        $produits = model(ProduitModel::class);
        $robot    = $produits->fiche($id);

        if ($robot === null) {
            throw PageNotFoundException::forPageNotFound('Ce robot n\'existe pas ou n\'est plus en vente.');
        }

        return view('store/produit', [
            'titre'       => $robot['nom'],
            'description' => mb_substr($robot['description'], 0, 150),
            'robot'       => $robot,
            'images'      => model(ImageModel::class)->duProduit($id),
            'compatibles' => $produits->compatibles($id),
        ]);
    }
}
```

- [ ] **Step 6 : Vues**

`app/Views/store/catalogue.php` :

```php
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Robotix Store</h1>
        <p>Robots humanoïdes livrés, installés et garantis 2 ans.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <form class="filtres row g-3 align-items-end" method="get" action="<?= site_url('store') ?>" role="search" aria-label="Filtrer les robots">
            <div class="col-sm-6 col-lg-3">
                <label class="form-label" for="q">Rechercher</label>
                <input class="form-control" type="search" id="q" name="q" value="<?= esc($filtres['q']) ?>" placeholder="Nom du robot">
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label" for="categorie">Catégorie</label>
                <select class="form-select" id="categorie" name="categorie">
                    <option value="">Toutes</option>
                    <?php foreach ($categories as $categorie): ?>
                        <option value="<?= $categorie['idCategorie'] ?>"<?= (int) $categorie['idCategorie'] === $filtres['categorie'] ? ' selected' : '' ?>><?= esc($categorie['libelle']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label" for="marque">Marque</label>
                <select class="form-select" id="marque" name="marque">
                    <option value="">Toutes</option>
                    <?php foreach ($marques as $marque): ?>
                        <option value="<?= $marque['idMarque'] ?>"<?= (int) $marque['idMarque'] === $filtres['marque'] ? ' selected' : '' ?>><?= esc($marque['nom']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label" for="prixMax">Prix max (€ TTC)</label>
                <input class="form-control" type="number" id="prixMax" name="prixMax" min="0" step="100" value="<?= $filtres['prixMax'] ?: '' ?>">
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label" for="tri">Trier par</label>
                <select class="form-select" id="tri" name="tri">
                    <?php foreach (['nouveautes' => 'Nouveautés', 'prix_asc' => 'Prix croissant', 'prix_desc' => 'Prix décroissant'] as $valeur => $libelle): ?>
                        <option value="<?= $valeur ?>"<?= $filtres['tri'] === $valeur ? ' selected' : '' ?>><?= $libelle ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-sm-6 col-lg-1 d-grid">
                <button class="btn btn-robotix" type="submit">OK</button>
            </div>
        </form>

        <p class="my-4" aria-live="polite">
            <strong><?= count($robots) ?> robot<?= count($robots) > 1 ? 's' : '' ?></strong>
            <?php if (array_filter($filtres) !== ['tri' => $filtres['tri']]): ?>
                · <a href="<?= site_url('store') ?>">Effacer les filtres</a>
            <?php endif ?>
        </p>

        <?php if ($robots === []): ?>
            <p class="text-center py-5">Aucun robot ne correspond à votre recherche.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($robots as $robot): ?>
                    <div class="col-sm-6 col-lg-4 col-xl-3"><?= view('partials/carte_robot', ['robot' => $robot]) ?></div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>
</section>

<?= $this->endSection() ?>
```

`app/Views/store/produit.php` :

```php
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="section">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= site_url('store') ?>">Store</a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('store') . '?categorie=' . $robot['idCategorie'] ?>"><?= esc($robot['categorie']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($robot['nom']) ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <div class="col-lg-6">
                <div class="galerie" data-galerie>
                    <button type="button" class="galerie__principale" data-lightbox="ouvrir" aria-label="Agrandir l'image">
                        <img class="img-fluid" id="image-principale" src="<?= base_url('images/robots/' . ($images[0]['fichier'] ?? 'defaut.svg')) ?>"
                             alt="<?= esc($images[0]['legende'] ?? $robot['nom']) ?>" width="400" height="500">
                    </button>
                    <div class="galerie__vignettes">
                        <?php foreach ($images as $index => $image): ?>
                            <button type="button" class="galerie__vignette<?= $index === 0 ? ' active' : '' ?>"
                                    data-index="<?= $index ?>" data-src="<?= base_url('images/robots/' . $image['fichier']) ?>"
                                    data-legende="<?= esc($image['legende'] ?? $robot['nom']) ?>" aria-label="Voir l'image <?= $index + 1 ?>">
                                <img src="<?= base_url('images/robots/' . $image['fichier']) ?>" alt="" width="80" height="100">
                            </button>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <p class="carte-robot__marque"><?= esc($robot['marque']) ?> · <?= esc($robot['categorie']) ?> · Réf. <?= esc($robot['reference']) ?></p>
                <h1><?= esc($robot['nom']) ?></h1>
                <p class="fiche__prix"><?= euros(prix_ttc($robot['prixHt'], $robot['tauxTva'])) ?> <small>TTC</small></p>
                <p class="text-secondary"><?= euros($robot['prixHt']) ?> HT · TVA <?= (float) $robot['tauxTva'] ?> %</p>
                <p>
                    <?php if ((int) $robot['stock'] > 0): ?>
                        <span class="badge text-bg-success">En stock (<?= (int) $robot['stock'] ?>)</span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary">Sur commande</span>
                    <?php endif ?>
                </p>
                <p class="lead"><?= nl2br(esc($robot['description'])) ?></p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a class="btn btn-robotix" href="<?= site_url('tarifs') . '?robot=' . $robot['idProduit'] ?>#calculateur">Configurer et calculer mon prix</a>
                    <a class="btn btn-contour" href="<?= site_url('contact') . '?objet=demo&amp;robot=' . $robot['idProduit'] ?>">Demander une démo</a>
                </div>
            </div>
        </div>

        <?php if ($compatibles !== []): ?>
            <h2 class="titre-section mt-5 pt-4">Robots compatibles</h2>
            <div class="row g-4 justify-content-center">
                <?php foreach ($compatibles as $compatible): ?>
                    <div class="col-sm-6 col-lg-3"><?= view('partials/carte_robot', ['robot' => $compatible]) ?></div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>
</section>

<?= $this->include('partials/anatomie') ?>

<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Galerie d'images" hidden>
    <button type="button" class="lightbox__fermer" data-lightbox="fermer" aria-label="Fermer">×</button>
    <button type="button" class="lightbox__nav lightbox__nav--precedent" data-lightbox="precedent" aria-label="Image précédente">‹</button>
    <figure class="lightbox__figure">
        <img id="lightbox-image" src="<?= base_url('images/robots/' . ($images[0]['fichier'] ?? 'defaut.svg')) ?>" alt="" width="400" height="500">
        <figcaption id="lightbox-legende"></figcaption>
    </figure>
    <button type="button" class="lightbox__nav lightbox__nav--suivant" data-lightbox="suivant" aria-label="Image suivante">›</button>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/galerie.js') ?>"></script>
<script src="<?= base_url('js/image-map.js') ?>"></script>
<?= $this->endSection() ?>
```

- [ ] **Step 7 : Script de la galerie**

`public/js/galerie.js` :

```js
/* Robotix — galerie de la fiche robot : vignettes (div) et visionneuse (popup) */
(function (racine) {
    'use strict';

    const indexSuivant = (i, n) => (i + 1) % n;
    const indexPrecedent = (i, n) => (i - 1 + n) % n;

    const api = { indexSuivant, indexPrecedent };
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
        return;
    }
    racine.Galerie = api;

    const galerie = document.querySelector('[data-galerie]');
    const lightbox = document.getElementById('lightbox');
    if (!galerie || !lightbox) {
        return;
    }

    const vignettes = Array.from(galerie.querySelectorAll('.galerie__vignette'));
    const principale = document.getElementById('image-principale');
    const imageGrande = document.getElementById('lightbox-image');
    const legende = document.getElementById('lightbox-legende');
    let courant = 0;
    let declencheur = null;

    function afficher(index) {
        courant = index;
        const vignette = vignettes[index];
        principale.src = vignette.dataset.src;
        principale.alt = vignette.dataset.legende;
        imageGrande.src = vignette.dataset.src;
        imageGrande.alt = vignette.dataset.legende;
        legende.textContent = vignette.dataset.legende + ' (' + (index + 1) + '/' + vignettes.length + ')';
        vignettes.forEach((v, i) => v.classList.toggle('active', i === index));
    }

    function ouvrir() {
        declencheur = document.activeElement;
        afficher(courant);
        lightbox.hidden = false;
        document.body.classList.add('sans-defilement');
        lightbox.querySelector('[data-lightbox="fermer"]').focus();
    }

    function fermer() {
        lightbox.hidden = true;
        document.body.classList.remove('sans-defilement');
        if (declencheur) {
            declencheur.focus();
        }
    }

    vignettes.forEach((vignette, index) => vignette.addEventListener('click', () => afficher(index)));
    galerie.querySelector('[data-lightbox="ouvrir"]').addEventListener('click', ouvrir);

    lightbox.addEventListener('click', (evenement) => {
        const action = evenement.target.dataset.lightbox;
        if (action === 'fermer' || evenement.target === lightbox) fermer();
        if (action === 'suivant') afficher(indexSuivant(courant, vignettes.length));
        if (action === 'precedent') afficher(indexPrecedent(courant, vignettes.length));
    });

    document.addEventListener('keydown', (evenement) => {
        if (lightbox.hidden) return;
        if (evenement.key === 'Escape') fermer();
        if (evenement.key === 'ArrowRight') afficher(indexSuivant(courant, vignettes.length));
        if (evenement.key === 'ArrowLeft') afficher(indexPrecedent(courant, vignettes.length));
    });
})(this);
```

- [ ] **Step 8 : Styles (ajout à la fin de `robotix.css`)**

```css
/* ---------- Catalogue ---------- */
.filtres { background: #fff; border-radius: var(--rbx-rayon); padding: 1.5rem; box-shadow: var(--rbx-ombre); }

/* ---------- Fiche robot et galerie ---------- */
.fiche__prix { font-family: var(--rbx-titres); font-size: 2rem; color: var(--rbx-violet); margin-bottom: .2rem; }
.galerie__principale {
    display: block; width: 100%; padding: 0; border: 0; cursor: zoom-in;
    border-radius: var(--rbx-rayon); overflow: hidden; box-shadow: var(--rbx-ombre); background: var(--rbx-nuit);
}
.galerie__principale img { width: 100%; height: auto; transition: transform .4s; }
.galerie__principale:hover img { transform: scale(1.04); }
.galerie__vignettes { display: flex; gap: .75rem; margin-top: 1rem; }
.galerie__vignette {
    padding: 0; border: 3px solid transparent; border-radius: .6rem; overflow: hidden;
    background: none; opacity: .7; transition: opacity .2s, border-color .2s;
}
.galerie__vignette:hover, .galerie__vignette.active { opacity: 1; border-color: var(--rbx-cyan); }
.galerie__vignette img { display: block; width: 80px; height: auto; }

/* ---------- Visionneuse (popup) ---------- */
.lightbox {
    position: fixed; inset: 0; z-index: 1080;
    display: flex; align-items: center; justify-content: center; gap: 1rem;
    background: rgba(11, 16, 32, .92); padding: 1rem;
}
.lightbox[hidden] { display: none; }
.lightbox__figure { margin: 0; text-align: center; color: #e2e8f0; }
.lightbox__figure img { max-width: min(90vw, 520px); max-height: 80vh; width: auto; height: auto; border-radius: var(--rbx-rayon); }
.lightbox__fermer, .lightbox__nav {
    background: rgba(255, 255, 255, .12); color: #fff; border: 0; border-radius: 50%;
    width: 3rem; height: 3rem; font-size: 1.8rem; line-height: 1;
}
.lightbox__fermer { position: absolute; top: 1rem; right: 1rem; }
.lightbox__fermer:hover, .lightbox__nav:hover { background: var(--rbx-cyan); color: var(--rbx-nuit); }
.sans-defilement { overflow: hidden; }
```

- [ ] **Step 9 : Lancer les tests pour vérifier qu'ils passent**

Run : `php vendor/bin/phpunit tests/feature/StoreTest.php` puis `node --test tests/js/`
Expected : PASS (8 tests PHP ; 5 tests JS au total).

- [ ] **Step 10 : Vérification dans le navigateur**

Sur `/store`, combiner les filtres ; sur une fiche, cliquer sur la vignette 2 (l'image principale change), cliquer sur l'image (popup), tester les flèches et Échap.

- [ ] **Step 11 : Point de contrôle** — suites PHP et JS vertes.

---

### Task 7 : Tarifs et calculateur JavaScript

**Files :**
- Create : `app/Libraries/Tarifs.php`
- Create : `app/Views/pages/tarifs.php`
- Create : `public/js/calculateur.js`
- Modify : `app/Controllers/Pages.php` (méthode `tarifs`), `app/Config/Routes.php`, `public/css/robotix.css`
- Test : `tests/unit/TarifsTest.php`, `tests/feature/TarifsPageTest.php`, `tests/js/calculateur.test.js`

**Interfaces :**
- Consumes : `ProduitModel::gammes()`, `pourCalculateur()` (tâche 5).
- Produces : `Tarifs::TAUX_TVA` (20.0), `Tarifs::options(): array` (`code`, `libelle`, `type` = `pourcentage`|`fixe`, `valeur`, `description`), `Tarifs::financements(): array` (`mois`, `taux`, `libelle`).
- Produces : dans `calculateur.js`, `Calculateur.calculer({prixHt, tauxTva, quantite, options: [{type, valeur}], mois, tauxAnnuel})` → `{optionsHt, totalHt, tva, totalTtc, mensualite, coutCredit}` ; `mensualiteCredit(capital, tauxAnnuel, mois)` ; `normaliserQuantite(valeur)` → entier entre 1 et 5 ; `quantiteValide(valeur)` → booléen.

- [ ] **Step 1 : Écrire les tests (échouent)**

`tests/unit/TarifsTest.php` :

```php
<?php

use App\Libraries\Tarifs;
use CodeIgniter\Test\CIUnitTestCase;

final class TarifsTest extends CIUnitTestCase
{
    public function testLesCodesOptionsSontUniques(): void
    {
        $codes = array_column(Tarifs::options(), 'code');

        $this->assertSame($codes, array_unique($codes));
        $this->assertContains('garantie', $codes);
    }

    public function testLesTypesOptionsSontConnus(): void
    {
        foreach (Tarifs::options() as $option) {
            $this->assertContains($option['type'], ['pourcentage', 'fixe']);
        }
    }

    public function testLesDureesDeFinancement(): void
    {
        $this->assertSame([1, 12, 24, 36], array_column(Tarifs::financements(), 'mois'));
    }
}
```

`tests/feature/TarifsPageTest.php` :

```php
<?php

use Tests\Support\RobotixTestCase;

final class TarifsPageTest extends RobotixTestCase
{
    public function testPresentationDesGammesEtServices(): void
    {
        $resultat = $this->get('tarifs');

        $resultat->assertOK();
        $resultat->assertSee('Nos gammes', 'h2');
        $resultat->assertSee('Compagnon');
        $resultat->assertSee('Premium');
        $resultat->assertSee('Garantie étendue 3 ans');
        $resultat->assertSee('418,80 €'); // Reachy Mini TTC : gamme Éducatif « à partir de »
    }

    public function testLeCalculateurEstPresentAvecSesDonnees(): void
    {
        $resultat = $this->get('tarifs');

        $resultat->assertSee('id="calculateur"');
        $resultat->assertSee('id="donnees-tarifs"');
        $resultat->assertSee('js/calculateur.js');
    }

    public function testLeRobotPasseEnParametreEstPreselectionne(): void
    {
        $id = $this->idProduit('RBX-FIG-02');

        $this->get('tarifs', ['robot' => $id])->assertSee('<option value="' . $id . '" selected>');
    }
}
```

`tests/js/calculateur.test.js` :

```js
const test = require('node:test');
const assert = require('node:assert');
const { calculer, mensualiteCredit, normaliserQuantite, quantiteValide } = require('../../public/js/calculateur.js');

const g1 = { prixHt: 13900, tauxTva: 20, quantite: 1, options: [], mois: 1, tauxAnnuel: 0 };

test('robot seul, payé comptant', () => {
    const r = calculer(g1);
    assert.deepStrictEqual(
        [r.totalHt, r.tva, r.totalTtc, r.mensualite, r.coutCredit],
        [13900, 2780, 16680, 16680, 0],
    );
});

test('garantie (12 % du prix HT) et livraison (290 € HT)', () => {
    const r = calculer({ ...g1, options: [{ type: 'pourcentage', valeur: 12 }, { type: 'fixe', valeur: 290 }] });
    assert.strictEqual(r.optionsHt, 1958);
    assert.strictEqual(r.totalHt, 15858);
    assert.strictEqual(r.tva, 3171.6);
    assert.strictEqual(r.totalTtc, 19029.6);
});

test('la quantité multiplie robot et options', () => {
    const r = calculer({ ...g1, quantite: 2, options: [{ type: 'pourcentage', valeur: 12 }, { type: 'fixe', valeur: 290 }] });
    assert.strictEqual(r.totalHt, 31716);
    assert.strictEqual(r.totalTtc, 38059.2);
});

test('12 mois sans frais : capital divisé par 12', () => {
    assert.strictEqual(mensualiteCredit(12000, 0, 12), 1000);
});

test('24 mois à 3,9 % : mensualité d\'un prêt amortissable', () => {
    assert.ok(Math.abs(mensualiteCredit(10000, 3.9, 24) - 433.80) < 0.05);
});

test('le coût du crédit est positif avec intérêts', () => {
    const r = calculer({ ...g1, mois: 36, tauxAnnuel: 5.9 });
    assert.ok(r.coutCredit > 0);
    assert.strictEqual(r.totalTtc, 16680);
});

test('quantités absurdes ramenées entre 1 et 5', () => {
    assert.deepStrictEqual(['0', '-3', 'abc', '99', '3', ''].map(normaliserQuantite), [1, 1, 1, 5, 3, 1]);
    assert.deepStrictEqual(['0', '-3', 'abc', '99', '3', '2.5'].map(quantiteValide), [false, false, false, false, true, false]);
});
```

- [ ] **Step 2 : Lancer les tests pour vérifier qu'ils échouent**

Run : `php vendor/bin/phpunit --filter Tarifs` puis `node --test tests/js/`
Expected : FAIL (classe `Tarifs` et route `tarifs` absentes ; module `calculateur.js` introuvable).

- [ ] **Step 3 : Grille des services**

`app/Libraries/Tarifs.php` :

```php
<?php

namespace App\Libraries;

/**
 * Grille des services et des financements Robotix (prix HT, TVA 20 %).
 * Partagée par la page Tarifs et par le calculateur JavaScript.
 */
final class Tarifs
{
    public const TAUX_TVA = 20.0;

    public static function options(): array
    {
        return [
            ['code' => 'garantie', 'libelle' => 'Garantie étendue 3 ans', 'type' => 'pourcentage', 'valeur' => 12.0,
             'description' => '12 % du prix HT du robot : pièces, main-d\'œuvre et robot de prêt.'],
            ['code' => 'livraison', 'libelle' => 'Livraison et installation', 'type' => 'fixe', 'valeur' => 290.0,
             'description' => 'Livraison à domicile, déballage, cartographie du logement et mise en service.'],
            ['code' => 'maintenance', 'libelle' => 'Contrat de maintenance (1 an)', 'type' => 'fixe', 'valeur' => 490.0,
             'description' => 'Deux visites d\'entretien et les mises à jour logicielles prioritaires.'],
            ['code' => 'formation', 'libelle' => 'Prise en main (2 h)', 'type' => 'fixe', 'valeur' => 150.0,
             'description' => 'Un technicien vous apprend à programmer les routines de votre robot.'],
        ];
    }

    public static function financements(): array
    {
        return [
            ['mois' => 1, 'taux' => 0.0, 'libelle' => 'Comptant'],
            ['mois' => 12, 'taux' => 0.0, 'libelle' => '12 mois sans frais'],
            ['mois' => 24, 'taux' => 3.9, 'libelle' => '24 mois (taux annuel 3,9 %)'],
            ['mois' => 36, 'taux' => 5.9, 'libelle' => '36 mois (taux annuel 5,9 %)'],
        ];
    }
}
```

- [ ] **Step 4 : Route et contrôleur**

Ajouter à `app/Config/Routes.php`, dans le bloc « Pages vitrine » :

```php
$routes->get('tarifs', 'Pages::tarifs');
```

Dans `app/Controllers/Pages.php`, ajouter `use App\Libraries\Tarifs;` et la méthode :

```php
    public function tarifs(): string
    {
        $robots = array_map(static fn (array $r): array => [
            'idProduit' => (int) $r['idProduit'],
            'nom'       => $r['nom'],
            'prixHt'    => (float) $r['prixHt'],
            'tauxTva'   => (float) $r['tauxTva'],
        ], model(ProduitModel::class)->pourCalculateur());

        return view('pages/tarifs', [
            'titre'        => 'Tarifs',
            'gammes'       => model(ProduitModel::class)->gammes(),
            'options'      => Tarifs::options(),
            'financements' => Tarifs::financements(),
            'robots'       => $robots,
            'robotChoisi'  => (int) $this->request->getGet('robot'),
        ]);
    }
```

- [ ] **Step 5 : Vue**

`app/Views/pages/tarifs.php` :

```php
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Tarifs</h1>
        <p>Des robots pour tous les budgets, des services pour en profiter sereinement.</p>
    </div>
</section>

<section class="section" aria-labelledby="titre-gammes">
    <div class="container">
        <h2 id="titre-gammes" class="titre-section">Nos gammes</h2>
        <div class="row g-4 mt-2">
            <?php foreach ($gammes as $gamme): ?>
                <div class="col-sm-6 col-lg-3">
                    <article class="gamme">
                        <h3 class="h5"><?= esc($gamme['libelle']) ?></h3>
                        <p class="gamme__prix"><small>à partir de</small><br><?= euros(prix_ttc($gamme['prixMin'], 20)) ?> <small>TTC</small></p>
                        <p><?= esc($gamme['description']) ?></p>
                        <p class="text-secondary"><?= (int) $gamme['nbRobots'] ?> robot<?= (int) $gamme['nbRobots'] > 1 ? 's' : '' ?></p>
                        <a class="btn btn-contour btn-sm" href="<?= site_url('store') . '?categorie=' . $gamme['idCategorie'] ?>">Voir la gamme</a>
                    </article>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</section>

<section class="section bg-white" aria-labelledby="titre-services">
    <div class="container">
        <h2 id="titre-services" class="titre-section">Services</h2>
        <div class="table-responsive mt-4">
            <table class="table table-hover align-middle tableau-tarifs">
                <caption>Prix des services, par robot</caption>
                <thead><tr><th scope="col">Service</th><th scope="col">Détail</th><th scope="col" class="text-end">Prix HT</th><th scope="col" class="text-end">Prix TTC</th></tr></thead>
                <tbody>
                    <?php foreach ($options as $option): ?>
                        <tr>
                            <th scope="row"><?= esc($option['libelle']) ?></th>
                            <td><?= esc($option['description']) ?></td>
                            <?php if ($option['type'] === 'pourcentage'): ?>
                                <td class="text-end" colspan="2"><?= (float) $option['valeur'] ?> % du prix HT du robot</td>
                            <?php else: ?>
                                <td class="text-end"><?= euros($option['valeur']) ?></td>
                                <td class="text-end"><?= euros(prix_ttc($option['valeur'], 20)) ?></td>
                            <?php endif ?>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section section--sombre" aria-labelledby="titre-calculateur">
    <div class="container">
        <h2 id="titre-calculateur" class="titre-section">Calculez votre prix</h2>
        <form id="calculateur" class="calculateur row g-4 mt-2" novalidate>
            <div class="col-lg-7">
                <div class="calculateur__panneau">
                    <div class="row g-3">
                        <div class="col-sm-8">
                            <label class="form-label" for="robot">Robot</label>
                            <select class="form-select" id="robot" name="robot">
                                <?php foreach ($robots as $robot): ?>
                                    <option value="<?= $robot['idProduit'] ?>"<?= $robot['idProduit'] === $robotChoisi ? ' selected' : '' ?>><?= esc($robot['nom']) ?> — <?= euros($robot['prixHt']) ?> HT</option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label" for="quantite">Quantité (1 à 5)</label>
                            <input class="form-control" type="number" id="quantite" name="quantite" min="1" max="5" step="1" value="1" required>
                            <div class="invalid-feedback">Saisissez un nombre entier entre 1 et 5.</div>
                        </div>
                    </div>
                    <fieldset class="mt-4">
                        <legend class="form-label">Options</legend>
                        <?php foreach ($options as $option): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="options" id="option-<?= $option['code'] ?>" value="<?= $option['code'] ?>">
                                <label class="form-check-label" for="option-<?= $option['code'] ?>">
                                    <?= esc($option['libelle']) ?>
                                    <span class="text-secondary-emphasis">(<?= $option['type'] === 'pourcentage' ? (float) $option['valeur'] . ' % du prix HT' : euros($option['valeur']) . ' HT' ?>)</span>
                                </label>
                            </div>
                        <?php endforeach ?>
                    </fieldset>
                    <fieldset class="mt-4">
                        <legend class="form-label">Financement</legend>
                        <?php foreach ($financements as $index => $financement): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="financement" id="financement-<?= $financement['mois'] ?>"
                                       value="<?= $financement['mois'] ?>"<?= $index === 0 ? ' checked' : '' ?>>
                                <label class="form-check-label" for="financement-<?= $financement['mois'] ?>"><?= esc($financement['libelle']) ?></label>
                            </div>
                        <?php endforeach ?>
                    </fieldset>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="calculateur__resultat" aria-live="polite">
                    <dl class="calculateur__lignes">
                        <dt>Robot(s) HT</dt><dd><output id="resultat-robot">—</output></dd>
                        <dt>Options HT</dt><dd><output id="resultat-options">—</output></dd>
                        <dt>Total HT</dt><dd><output id="resultat-ht">—</output></dd>
                        <dt>TVA 20 %</dt><dd><output id="resultat-tva">—</output></dd>
                        <dt class="calculateur__total">Total TTC</dt><dd class="calculateur__total"><output id="resultat-ttc">—</output></dd>
                        <dt>Mensualité</dt><dd><output id="resultat-mensualite">—</output></dd>
                        <dt>Coût du crédit</dt><dd><output id="resultat-cout">—</output></dd>
                    </dl>
                    <a class="btn btn-robotix w-100" id="lien-devis" href="<?= site_url('contact') ?>?objet=devis">Demander un devis</a>
                </div>
            </div>
        </form>
        <script type="application/json" id="donnees-tarifs"><?= json_encode(
            ['robots' => $robots, 'options' => $options, 'financements' => $financements],
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE
        ) ?></script>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/calculateur.js') ?>"></script>
<?= $this->endSection() ?>
```

- [ ] **Step 6 : Script du calculateur**

`public/js/calculateur.js` :

```js
/* Robotix — calculateur de prix : robot + options + quantité + financement */
(function (racine) {
    'use strict';

    const QUANTITE_MIN = 1;
    const QUANTITE_MAX = 5;

    const arrondir = (x) => Math.round(x * 100) / 100;

    /** Mensualité d'un prêt amortissable ; 1 mois = paiement comptant. */
    function mensualiteCredit(capital, tauxAnnuel, mois) {
        if (mois <= 1) return arrondir(capital);
        const t = tauxAnnuel / 100 / 12;
        if (t === 0) return arrondir(capital / mois);
        return arrondir((capital * t) / (1 - Math.pow(1 + t, -mois)));
    }

    function calculer(entree) {
        const optionsUnitaires = entree.options.reduce(
            (somme, option) => somme + (option.type === 'pourcentage' ? (entree.prixHt * option.valeur) / 100 : option.valeur),
            0,
        );
        const totalHt = arrondir((entree.prixHt + optionsUnitaires) * entree.quantite);
        const tva = arrondir((totalHt * entree.tauxTva) / 100);
        const totalTtc = arrondir(totalHt + tva);
        const mensualite = mensualiteCredit(totalTtc, entree.tauxAnnuel, entree.mois);
        const coutCredit = Math.max(0, arrondir(mensualite * Math.max(1, entree.mois) - totalTtc));

        return { optionsHt: arrondir(optionsUnitaires * entree.quantite), totalHt, tva, totalTtc, mensualite, coutCredit };
    }

    function quantiteValide(valeur) {
        return /^\d+$/.test(String(valeur).trim()) && Number(valeur) >= QUANTITE_MIN && Number(valeur) <= QUANTITE_MAX;
    }

    function normaliserQuantite(valeur) {
        const n = parseInt(valeur, 10);
        if (Number.isNaN(n) || n < QUANTITE_MIN) return QUANTITE_MIN;
        return Math.min(n, QUANTITE_MAX);
    }

    const api = { calculer, mensualiteCredit, quantiteValide, normaliserQuantite };
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
        return;
    }
    racine.Calculateur = api;

    const formulaire = document.getElementById('calculateur');
    if (!formulaire) return;

    const donnees = JSON.parse(document.getElementById('donnees-tarifs').textContent);
    const champQuantite = document.getElementById('quantite');
    const lienDevis = document.getElementById('lien-devis');
    const euros = (x) => x.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' });
    const ecrire = (id, texte) => { document.getElementById(id).textContent = texte; };

    function mettreAJour() {
        const robot = donnees.robots.find((r) => r.idProduit === Number(formulaire.robot.value));
        const valide = quantiteValide(champQuantite.value);
        champQuantite.classList.toggle('is-invalid', !valide);
        const quantite = normaliserQuantite(champQuantite.value);

        const codes = Array.from(formulaire.querySelectorAll('input[name="options"]:checked')).map((c) => c.value);
        const options = donnees.options.filter((o) => codes.includes(o.code));
        const financement = donnees.financements.find((f) => f.mois === Number(formulaire.financement.value));

        const r = calculer({
            prixHt: robot.prixHt, tauxTva: robot.tauxTva, quantite, options,
            mois: financement.mois, tauxAnnuel: financement.taux,
        });

        ecrire('resultat-robot', euros(robot.prixHt * quantite));
        ecrire('resultat-options', euros(r.optionsHt));
        ecrire('resultat-ht', euros(r.totalHt));
        ecrire('resultat-tva', euros(r.tva));
        ecrire('resultat-ttc', euros(r.totalTtc));
        ecrire('resultat-mensualite', financement.mois > 1 ? euros(r.mensualite) + ' × ' + financement.mois + ' mois' : 'Paiement comptant');
        ecrire('resultat-cout', euros(r.coutCredit));
        lienDevis.href = lienDevis.href.split('?')[0] + '?objet=devis&robot=' + robot.idProduit;
    }

    formulaire.addEventListener('input', mettreAJour);
    formulaire.addEventListener('change', mettreAJour);
    champQuantite.addEventListener('blur', () => { champQuantite.value = normaliserQuantite(champQuantite.value); mettreAJour(); });
    formulaire.addEventListener('submit', (evenement) => evenement.preventDefault());
    mettreAJour();
})(this);
```

- [ ] **Step 7 : Styles (ajout à la fin de `robotix.css`)**

```css
/* ---------- Tarifs ---------- */
.gamme {
    background: #fff; border-radius: var(--rbx-rayon); padding: 1.8rem; height: 100%;
    box-shadow: var(--rbx-ombre); text-align: center; border-bottom: 4px solid var(--rbx-violet);
}
.gamme__prix { font-family: var(--rbx-titres); font-size: 1.5rem; color: var(--rbx-violet); }
.gamme__prix small { font-family: var(--rbx-police); font-size: .8rem; color: var(--rbx-gris); }
.tableau-tarifs caption { caption-side: top; color: var(--rbx-gris); }

/* ---------- Calculateur ---------- */
.calculateur__panneau, .calculateur__resultat {
    background: var(--rbx-nuit-2); border: 1px solid #1e293b; border-radius: var(--rbx-rayon); padding: 1.8rem; height: 100%;
}
.calculateur .form-label, .calculateur legend { color: #e2e8f0; font-weight: 600; }
.calculateur .form-check-label { color: #cbd5e1; }
.calculateur__lignes { display: grid; grid-template-columns: 1fr auto; gap: .6rem 1rem; margin-bottom: 1.5rem; }
.calculateur__lignes dt { font-weight: 400; color: #94a3b8; }
.calculateur__lignes dd { margin: 0; text-align: right; color: #fff; font-variant-numeric: tabular-nums; }
.calculateur__total { font-size: 1.3rem; font-weight: 700; color: var(--rbx-cyan) !important; border-top: 1px solid #334155; padding-top: .6rem; }
```

- [ ] **Step 8 : Lancer les tests pour vérifier qu'ils passent**

Run : `php vendor/bin/phpunit --filter Tarifs` puis `node --test tests/js/`
Expected : PASS (6 tests PHP ; 12 tests JS au total).

- [ ] **Step 9 : Vérification dans le navigateur**

Sur `/tarifs` : choisir Unitree G1 et cocher garantie et livraison → total TTC 19 029,60 € ; quantité 2 → 38 059,20 € ; saisir 9 → champ en rouge, et la valeur revient à 5 à la sortie du champ ; 24 mois → une mensualité et un coût du crédit s'affichent.

- [ ] **Step 10 : Point de contrôle** — suites PHP et JS vertes.

---

### Task 8 : API JSON des événements et des showrooms

**Files :**
- Create : `app/Controllers/Api.php`
- Modify : `app/Config/Routes.php`
- Test : `tests/feature/ApiTest.php`

**Interfaces :**
- Consumes : `EvenementModel::duMois()`, `EvenementModel::TYPES`, `ShowroomModel` (tâche 5).
- Produces : `GET api/evenements?mois=AAAA-MM` → liste JSON `{id, titre, description, type, typeLibelle, debut, fin, idShowroom, showroom, ville, idProduit, produit, urlProduit}` (`debut` et `fin` au format `AAAA-MM-JJTHH:MM:SS`, `idShowroom` et `idProduit` entiers ou `null`) ; HTTP 400 si `mois` est invalide ; sans `mois` : mois courant.
- Produces : `GET api/showrooms` → liste JSON `{id, nom, adresse, codePostal, ville, latitude, longitude, telephone, horaires}` (latitude et longitude en nombres), triée par ville.

- [ ] **Step 1 : Écrire le test (échoue)**

`tests/feature/ApiTest.php` :

```php
<?php

use Tests\Support\RobotixTestCase;

final class ApiTest extends RobotixTestCase
{
    public function testEvenementsDOctobre2026(): void
    {
        $resultat = $this->get('api/evenements', ['mois' => '2026-10']);

        $resultat->assertOK();
        $evenements = json_decode($resultat->getJSON(), true);
        $this->assertCount(4, $evenements);
        $this->assertSame('Démonstration Unitree G1', $evenements[0]['titre']);
        $this->assertSame('2026-10-08T14:00:00', $evenements[0]['debut']);
        $this->assertSame('Démonstration', $evenements[0]['typeLibelle']);
        $this->assertIsInt($evenements[0]['idShowroom']);
        $this->assertStringContainsString('store/robot/', $evenements[0]['urlProduit']);
    }

    public function testEvenementHorsShowroom(): void
    {
        $evenements = json_decode($this->get('api/evenements', ['mois' => '2026-10'])->getJSON(), true);
        $salon = array_values(array_filter($evenements, static fn ($e) => $e['type'] === 'salon'))[0];

        $this->assertNull($salon['idShowroom']);
        $this->assertNull($salon['urlProduit']);
        $this->assertSame('2026-10-25T19:00:00', $salon['fin']);
    }

    public function testMoisInvalideRenvoie400(): void
    {
        foreach (['2026-13', 'octobre', '2026-1', "2026-10' OR 1=1"] as $mois) {
            $this->get('api/evenements', ['mois' => $mois])->assertStatus(400);
        }
    }

    public function testSansMoisRenvoieLeMoisCourant(): void
    {
        $this->get('api/evenements')->assertOK();
    }

    public function testShowroomsAvecCoordonneesNumeriques(): void
    {
        $showrooms = json_decode($this->get('api/showrooms')->getJSON(), true);

        $this->assertCount(3, $showrooms);
        $this->assertSame('Lyon', $showrooms[0]['ville']);
        $this->assertIsFloat($showrooms[0]['latitude']);
        $this->assertEqualsWithDelta(45.7612, $showrooms[0]['latitude'], 0.0001);
    }
}
```

- [ ] **Step 2 : Lancer le test pour vérifier qu'il échoue**

Run : `php vendor/bin/phpunit tests/feature/ApiTest.php`
Expected : FAIL (route `api/evenements` inconnue).

- [ ] **Step 3 : Routes**

Ajouter à `app/Config/Routes.php` :

```php
// API JSON (calendrier et carte) — exclue du filtre CSRF
$routes->get('api/evenements', 'Api::evenements');
$routes->get('api/showrooms', 'Api::showrooms');
```

- [ ] **Step 4 : Contrôleur**

`app/Controllers/Api.php` :

```php
<?php

namespace App\Controllers;

use App\Models\EvenementModel;
use App\Models\ShowroomModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Données JSON lues par calendrier.js et carte.js.
 */
class Api extends BaseController
{
    use ResponseTrait;

    protected $format = 'json';

    public function evenements(): ResponseInterface
    {
        $mois = (string) ($this->request->getGet('mois') ?? date('Y-m'));

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mois)) {
            return $this->failValidationErrors('Paramètre « mois » invalide : format attendu AAAA-MM.');
        }

        $evenements = model(EvenementModel::class)->duMois($mois);

        return $this->respond(array_map([$this, 'formaterEvenement'], $evenements));
    }

    public function showrooms(): ResponseInterface
    {
        $showrooms = model(ShowroomModel::class)->orderBy('ville')->findAll();

        return $this->respond(array_map(static fn (array $s): array => [
            'id'         => (int) $s['idShowroom'],
            'nom'        => $s['nom'],
            'adresse'    => $s['adresse'],
            'codePostal' => $s['codePostal'],
            'ville'      => $s['ville'],
            'latitude'   => (float) $s['latitude'],
            'longitude'  => (float) $s['longitude'],
            'telephone'  => $s['telephone'],
            'horaires'   => $s['horaires'],
        ], $showrooms));
    }

    private function formaterEvenement(array $e): array
    {
        $idProduit = $e['idProduit'] === null ? null : (int) $e['idProduit'];

        return [
            'id'          => (int) $e['idEvenement'],
            'titre'       => $e['titre'],
            'description' => $e['description'],
            'type'        => $e['type'],
            'typeLibelle' => EvenementModel::TYPES[$e['type']] ?? $e['type'],
            'debut'       => date('Y-m-d\TH:i:s', strtotime(substr($e['dateDebut'], 0, 19))),
            'fin'         => date('Y-m-d\TH:i:s', strtotime(substr($e['dateFin'], 0, 19))),
            'idShowroom'  => $e['idShowroom'] === null ? null : (int) $e['idShowroom'],
            'showroom'    => $e['showroom'],
            'ville'       => $e['ville'],
            'idProduit'   => $idProduit,
            'produit'     => $e['produit'],
            'urlProduit'  => $idProduit === null ? null : site_url('store/robot/' . $idProduit),
        ];
    }
}
```

- [ ] **Step 5 : Lancer le test pour vérifier qu'il passe**

Run : `php vendor/bin/phpunit tests/feature/ApiTest.php`
Expected : PASS (5 tests). Si `debut` vaut `1970-01-01…`, le pilote renvoie des objets `DateTime` et non des chaînes : vérifier dans `vendor/codeigniter4/framework/system/Database/SQLSRV/Connection.php` que `ReturnDatesAsStrings` vaut `1`.

- [ ] **Step 6 : Point de contrôle** — `php vendor/bin/phpunit` : tout est vert.

---

### Task 9 : Calendrier des événements

**Files :**
- Create : `app/Views/pages/evenements.php`
- Create : `public/js/calendrier.js`
- Modify : `app/Controllers/Pages.php` (méthode `evenements`), `app/Config/Routes.php`, `public/css/robotix.css`
- Test : `tests/feature/EvenementsPageTest.php`, `tests/js/calendrier.test.js`

**Interfaces :**
- Consumes : `GET api/evenements` (tâche 8), `EvenementModel::TYPES`, `ShowroomModel`.
- Produces : dans `calendrier.js`, `Calendrier.grilleMois(annee, mois)` → 42 cases `{date: 'AAAA-MM-JJ', jour, dansLeMois}` commençant un lundi ; `decalerMois(annee, mois, delta)` → `{annee, mois}` ; `evenementsDuJour(evenements, dateIso)` ; `filtrer(evenements, type, idShowroom)` ; `cleMois(annee, mois)` → `'AAAA-MM'` ; `titreMois(annee, mois)` → `'Octobre 2026'`.

- [ ] **Step 1 : Écrire les tests (échouent)**

`tests/feature/EvenementsPageTest.php` :

```php
<?php

use Tests\Support\RobotixTestCase;

final class EvenementsPageTest extends RobotixTestCase
{
    public function testLaPageContientLeCalendrierEtSesFiltres(): void
    {
        $resultat = $this->get('evenements');

        $resultat->assertOK();
        $resultat->assertSee('id="calendrier"');
        $resultat->assertSee('api/evenements');
        $resultat->assertSee('id="filtre-type"');
        $resultat->assertSee('Démonstration');
        $resultat->assertSee('Robotix Paris Opéra');
        $resultat->assertSee('js/calendrier.js');
    }

    public function testLeMoisDemandeEstTransmisAuCalendrier(): void
    {
        $this->get('evenements', ['mois' => '2026-12'])->assertSee('data-mois="2026-12"');
    }

    public function testUnMoisInvalideEstIgnore(): void
    {
        $this->get('evenements', ['mois' => '<script>'])->assertSee('data-mois="' . date('Y-m') . '"');
    }
}
```

`tests/js/calendrier.test.js` :

```js
const test = require('node:test');
const assert = require('node:assert');
const C = require('../../public/js/calendrier.js');

test('la grille d\'octobre 2026 commence le lundi 28 septembre', () => {
    const cases = C.grilleMois(2026, 10);
    assert.strictEqual(cases.length, 42);
    assert.deepStrictEqual(cases[0], { date: '2026-09-28', jour: 28, dansLeMois: false });
    assert.deepStrictEqual(cases[3], { date: '2026-10-01', jour: 1, dansLeMois: true });
    assert.strictEqual(cases.filter((c) => c.dansLeMois).length, 31);
});

test('changement d\'année dans les deux sens', () => {
    assert.deepStrictEqual(C.decalerMois(2026, 12, 1), { annee: 2027, mois: 1 });
    assert.deepStrictEqual(C.decalerMois(2026, 1, -1), { annee: 2025, mois: 12 });
});

const evenements = [
    { id: 1, type: 'demo', idShowroom: 1, debut: '2026-10-08T14:00:00', fin: '2026-10-08T17:00:00' },
    { id: 2, type: 'salon', idShowroom: null, debut: '2026-10-24T09:00:00', fin: '2026-10-25T19:00:00' },
];

test('un événement sur deux jours apparaît les deux jours', () => {
    assert.deepStrictEqual(C.evenementsDuJour(evenements, '2026-10-25').map((e) => e.id), [2]);
    assert.deepStrictEqual(C.evenementsDuJour(evenements, '2026-10-26'), []);
});

test('filtres par type et par showroom', () => {
    assert.deepStrictEqual(C.filtrer(evenements, 'salon', '').map((e) => e.id), [2]);
    assert.deepStrictEqual(C.filtrer(evenements, '', '1').map((e) => e.id), [1]);
    assert.strictEqual(C.filtrer(evenements, '', '').length, 2);
});

test('clé et titre du mois', () => {
    assert.strictEqual(C.cleMois(2026, 3), '2026-03');
    assert.strictEqual(C.titreMois(2026, 10), 'Octobre 2026');
});
```

- [ ] **Step 2 : Lancer les tests pour vérifier qu'ils échouent**

Run : `php vendor/bin/phpunit tests/feature/EvenementsPageTest.php` puis `node --test tests/js/`
Expected : FAIL.

- [ ] **Step 3 : Route et contrôleur**

Ajouter à `app/Config/Routes.php`, bloc « Pages vitrine » :

```php
$routes->get('evenements', 'Pages::evenements');
```

Dans `app/Controllers/Pages.php`, ajouter `use App\Models\ShowroomModel;` et :

```php
    public function evenements(): string
    {
        $mois = (string) $this->request->getGet('mois');

        return view('pages/evenements', [
            'titre'     => 'Événements',
            'mois'      => preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mois) ? $mois : date('Y-m'),
            'types'     => EvenementModel::TYPES,
            'showrooms' => model(ShowroomModel::class)->orderBy('ville')->findAll(),
        ]);
    }
```

- [ ] **Step 4 : Vue**

`app/Views/pages/evenements.php` :

```php
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Événements</h1>
        <p>Démonstrations, lancements, ateliers et salons : venez rencontrer nos robots.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="calendrier" id="calendrier" data-api="<?= site_url('api/evenements') ?>" data-mois="<?= esc($mois) ?>">
                    <div class="calendrier__barre">
                        <button type="button" class="btn btn-contour btn-sm" id="mois-precedent" aria-label="Mois précédent">‹</button>
                        <h2 class="calendrier__titre" id="calendrier-titre" aria-live="polite">Calendrier</h2>
                        <button type="button" class="btn btn-contour btn-sm" id="mois-suivant" aria-label="Mois suivant">›</button>
                        <button type="button" class="btn btn-link btn-sm" id="mois-courant">Aujourd'hui</button>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="filtre-type">Type</label>
                            <select class="form-select form-select-sm" id="filtre-type">
                                <option value="">Tous les types</option>
                                <?php foreach ($types as $code => $libelle): ?>
                                    <option value="<?= $code ?>"><?= esc($libelle) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="filtre-showroom">Lieu</label>
                            <select class="form-select form-select-sm" id="filtre-showroom">
                                <option value="">Tous les lieux</option>
                                <?php foreach ($showrooms as $showroom): ?>
                                    <option value="<?= $showroom['idShowroom'] ?>"><?= esc($showroom['nom']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="calendrier__table">
                            <caption class="visually-hidden">Événements du mois affiché</caption>
                            <thead>
                                <tr>
                                    <?php foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $jour): ?>
                                        <th scope="col"><?= $jour ?></th>
                                    <?php endforeach ?>
                                </tr>
                            </thead>
                            <tbody id="calendrier-corps"></tbody>
                        </table>
                    </div>
                    <p class="text-danger mt-2" id="calendrier-erreur" role="alert" hidden></p>
                    <noscript><p>Activez JavaScript pour afficher le calendrier.</p></noscript>
                    <ul class="legende">
                        <?php foreach ($types as $code => $libelle): ?>
                            <li><span class="pastille pastille--<?= $code ?>"></span><?= esc($libelle) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4">
                <aside class="detail-jour" id="detail-jour" aria-live="polite">
                    <h2 class="h5">Détail du jour</h2>
                    <p class="mb-0">Cliquez sur un jour pour voir ses événements.</p>
                </aside>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/calendrier.js') ?>"></script>
<?= $this->endSection() ?>
```

- [ ] **Step 5 : Script du calendrier**

`public/js/calendrier.js` :

```js
/* Robotix — calendrier mensuel des événements (données : api/evenements) */
(function (racine) {
    'use strict';

    const MOIS = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

    /** 42 cases (6 semaines) commençant un lundi, calculées en UTC pour éviter les décalages d'heure d'été. */
    function grilleMois(annee, mois) {
        const premier = new Date(Date.UTC(annee, mois - 1, 1));
        const decalage = (premier.getUTCDay() + 6) % 7; // lundi = 0
        const cases = [];
        for (let i = 0; i < 42; i++) {
            const d = new Date(Date.UTC(annee, mois - 1, 1 - decalage + i));
            cases.push({ date: d.toISOString().slice(0, 10), jour: d.getUTCDate(), dansLeMois: d.getUTCMonth() === mois - 1 });
        }
        return cases;
    }

    function decalerMois(annee, mois, delta) {
        const d = new Date(Date.UTC(annee, mois - 1 + delta, 1));
        return { annee: d.getUTCFullYear(), mois: d.getUTCMonth() + 1 };
    }

    const evenementsDuJour = (evenements, dateIso) =>
        evenements.filter((e) => e.debut.slice(0, 10) <= dateIso && e.fin.slice(0, 10) >= dateIso);

    const filtrer = (evenements, type, idShowroom) =>
        evenements.filter((e) => (!type || e.type === type) && (!idShowroom || e.idShowroom === Number(idShowroom)));

    const cleMois = (annee, mois) => annee + '-' + String(mois).padStart(2, '0');

    const titreMois = (annee, mois) => {
        const nom = MOIS[mois - 1];
        return nom.charAt(0).toUpperCase() + nom.slice(1) + ' ' + annee;
    };

    const api = { grilleMois, decalerMois, evenementsDuJour, filtrer, cleMois, titreMois };
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
        return;
    }
    racine.Calendrier = api;

    const conteneur = document.getElementById('calendrier');
    if (!conteneur) return;

    const corps = document.getElementById('calendrier-corps');
    const titre = document.getElementById('calendrier-titre');
    const erreur = document.getElementById('calendrier-erreur');
    const detail = document.getElementById('detail-jour');
    const filtreType = document.getElementById('filtre-type');
    const filtreShowroom = document.getElementById('filtre-showroom');
    const aujourdhui = new Date();
    const cleAujourdhui = cleMois(aujourdhui.getFullYear(), aujourdhui.getMonth() + 1) + '-' + String(aujourdhui.getDate()).padStart(2, '0');

    const [anneeInitiale, moisInitial] = conteneur.dataset.mois.split('-').map(Number);
    const etat = { annee: anneeInitiale, mois: moisInitial, evenements: [], jourChoisi: null };

    const heure = (iso) => new Date(iso).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    const dateLongue = (iso) => new Date(iso + 'T12:00:00').toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });

    function element(balise, classe, texte) {
        const el = document.createElement(balise);
        if (classe) el.className = classe;
        if (texte !== undefined) el.textContent = texte;
        return el;
    }

    async function charger() {
        titre.textContent = titreMois(etat.annee, etat.mois);
        erreur.hidden = true;
        try {
            const reponse = await fetch(conteneur.dataset.api + '?mois=' + cleMois(etat.annee, etat.mois));
            if (!reponse.ok) throw new Error('HTTP ' + reponse.status);
            etat.evenements = await reponse.json();
        } catch (e) {
            etat.evenements = [];
            erreur.textContent = 'Impossible de charger les événements. Réessayez plus tard.';
            erreur.hidden = false;
        }
        dessiner();
    }

    function visibles() {
        return filtrer(etat.evenements, filtreType.value, filtreShowroom.value);
    }

    function dessiner() {
        const evenements = visibles();
        const cases = grilleMois(etat.annee, etat.mois);
        corps.replaceChildren();

        for (let semaine = 0; semaine < 6; semaine++) {
            const ligne = document.createElement('tr');
            cases.slice(semaine * 7, semaine * 7 + 7).forEach((c) => {
                const cellule = document.createElement('td');
                const bouton = element('button', 'calendrier__jour');
                const duJour = evenementsDuJour(evenements, c.date);
                bouton.type = 'button';
                bouton.dataset.date = c.date;
                bouton.classList.toggle('hors-mois', !c.dansLeMois);
                bouton.classList.toggle('aujourdhui', c.date === cleAujourdhui);
                bouton.classList.toggle('choisi', c.date === etat.jourChoisi);
                bouton.setAttribute('aria-label', dateLongue(c.date) + ', ' + duJour.length + ' événement(s)');
                bouton.append(element('span', 'calendrier__numero', String(c.jour)));
                const pastilles = element('span', 'calendrier__pastilles');
                duJour.forEach((e) => pastilles.append(element('span', 'pastille pastille--' + e.type)));
                bouton.append(pastilles);
                bouton.addEventListener('click', () => { etat.jourChoisi = c.date; dessiner(); afficherDetail(c.date); });
                cellule.append(bouton);
                ligne.append(cellule);
            });
            corps.append(ligne);
        }
    }

    function afficherDetail(dateIso) {
        const duJour = evenementsDuJour(visibles(), dateIso);
        const titreDetail = element('h2', 'h5', dateLongue(dateIso));
        detail.replaceChildren(titreDetail);

        if (duJour.length === 0) {
            detail.append(element('p', 'mb-0', 'Aucun événement ce jour-là.'));
            return;
        }

        duJour.forEach((e) => {
            const carte = element('article', 'detail-jour__evenement');
            const type = element('p', 'mb-1 small');
            type.append(element('span', 'pastille pastille--' + e.type), document.createTextNode(e.typeLibelle));
            carte.append(type, element('h3', 'h6', e.titre));
            carte.append(element('p', 'mb-1', heure(e.debut) + ' – ' + heure(e.fin) + ' · ' + (e.showroom || 'Hors showroom')));
            if (e.description) carte.append(element('p', 'mb-1 text-secondary', e.description));
            if (e.urlProduit) {
                const lien = element('a', '', 'Voir le robot ' + e.produit + ' →');
                lien.href = e.urlProduit;
                carte.append(lien);
            }
            detail.append(carte);
        });
    }

    function changerMois(delta) {
        Object.assign(etat, decalerMois(etat.annee, etat.mois, delta), { jourChoisi: null });
        charger();
    }

    document.getElementById('mois-precedent').addEventListener('click', () => changerMois(-1));
    document.getElementById('mois-suivant').addEventListener('click', () => changerMois(1));
    document.getElementById('mois-courant').addEventListener('click', () => {
        Object.assign(etat, { annee: aujourdhui.getFullYear(), mois: aujourdhui.getMonth() + 1, jourChoisi: cleAujourdhui });
        charger().then(() => afficherDetail(cleAujourdhui));
    });
    [filtreType, filtreShowroom].forEach((filtre) => filtre.addEventListener('change', () => {
        dessiner();
        if (etat.jourChoisi) afficherDetail(etat.jourChoisi);
    }));

    charger();
})(this);
```

- [ ] **Step 6 : Styles (ajout à la fin de `robotix.css`)**

```css
/* ---------- Calendrier ---------- */
.calendrier, .detail-jour { background: #fff; border-radius: var(--rbx-rayon); padding: 1.5rem; box-shadow: var(--rbx-ombre); }
.calendrier__barre { display: flex; align-items: center; gap: .75rem; margin-bottom: 1rem; flex-wrap: wrap; }
.calendrier__titre { font-size: 1.3rem; margin: 0; min-width: 12rem; text-align: center; }
.calendrier__table { width: 100%; table-layout: fixed; border-collapse: separate; border-spacing: 4px; }
.calendrier__table th { text-align: center; font-size: .8rem; color: var(--rbx-gris); text-transform: uppercase; }
.calendrier__jour {
    width: 100%; min-height: 4.2rem; padding: .35rem; border: 1px solid #e2e8f0; border-radius: .6rem;
    background: var(--rbx-clair); display: flex; flex-direction: column; align-items: flex-start; gap: .3rem;
    transition: border-color .2s, background .2s;
}
.calendrier__jour:hover, .calendrier__jour:focus-visible { border-color: var(--rbx-cyan); background: #fff; }
.calendrier__jour.hors-mois { opacity: .4; }
.calendrier__jour.aujourdhui .calendrier__numero { background: var(--rbx-cyan); color: var(--rbx-nuit); border-radius: 50%; padding: 0 .4rem; }
.calendrier__jour.choisi { border: 2px solid var(--rbx-violet); background: #fff; }
.calendrier__numero { font-weight: 700; font-size: .9rem; }
.calendrier__pastilles .pastille { margin-right: .15rem; }
.legende { list-style: none; display: flex; flex-wrap: wrap; gap: 1rem; padding: 0; margin: 1rem 0 0; font-size: .9rem; }
.detail-jour { position: sticky; top: 5rem; }
.detail-jour__evenement { border-left: 3px solid var(--rbx-cyan); padding-left: .9rem; margin-top: 1.2rem; }
@media (max-width: 575.98px) {
    .calendrier__jour { min-height: 3rem; padding: .2rem; }
    .calendrier__numero { font-size: .8rem; }
}
```

- [ ] **Step 7 : Lancer les tests pour vérifier qu'ils passent**

Run : `php vendor/bin/phpunit tests/feature/EvenementsPageTest.php` puis `node --test tests/js/`
Expected : PASS (3 tests PHP ; 17 tests JS au total).

- [ ] **Step 8 : Vérification dans le navigateur**

Sur `/evenements` : octobre 2026 affiche des pastilles les 8, 15, 21, 24 et 25 ; un clic sur le 24 affiche le salon ; le filtre « Atelier » ne garde que le 21 ; « › » passe à novembre ; à 375 px la grille tient sans défilement de la page.

- [ ] **Step 9 : Point de contrôle** — suites PHP et JS vertes.

---

### Task 10 : Showrooms et Google Maps

**Files :**
- Create : `app/Views/pages/showrooms.php`
- Create : `public/js/carte.js`
- Modify : `app/Controllers/Pages.php` (méthode `showrooms`), `app/Config/Routes.php`, `public/css/robotix.css`
- Test : `tests/feature/ShowroomsPageTest.php`, `tests/js/carte.test.js`

**Interfaces :**
- Consumes : `GET api/showrooms` (tâche 8), `ShowroomModel`.
- Produces : dans `carte.js`, `Carte.urlCarte(lat, lng, zoom)` → `https://maps.google.com/maps?q=LAT,LNG&z=ZOOM&output=embed` ; `urlItineraire(lat, lng)` → `https://www.google.com/maps/dir/?api=1&destination=LAT,LNG` ; `distanceKm(lat1, lng1, lat2, lng2)` (formule de haversine) ; `plusProche({lat, lng}, showrooms)` → `{showroom, distance}` ; `bornerZoom(z)` → entier entre 5 et 20.

- [ ] **Step 1 : Écrire les tests (échouent)**

`tests/feature/ShowroomsPageTest.php` :

```php
<?php

use Tests\Support\RobotixTestCase;

final class ShowroomsPageTest extends RobotixTestCase
{
    public function testLaCarteGoogleEstAfficheeSurLePremierShowroom(): void
    {
        $resultat = $this->get('showrooms');

        $resultat->assertOK();
        $resultat->assertSee('<iframe');
        $resultat->assertSee('https://maps.google.com/maps?q=45.7612,4.8562&amp;z=15&amp;output=embed');
        $resultat->assertSee('id="btn-proche"');
        $resultat->assertSee('id="zoom-plus"');
        $resultat->assertSee('api/showrooms');
        $resultat->assertSee('js/carte.js');
    }

    public function testListeDeSecoursSansJavascript(): void
    {
        $this->get('showrooms')->assertSee('Robotix Marseille Vieux-Port');
    }
}
```

`tests/js/carte.test.js` :

```js
const test = require('node:test');
const assert = require('node:assert');
const C = require('../../public/js/carte.js');

const showrooms = [
    { id: 1, ville: 'Lyon', latitude: 45.7612, longitude: 4.8562 },
    { id: 2, ville: 'Marseille', latitude: 43.2965, longitude: 5.3698 },
    { id: 3, ville: 'Paris', latitude: 48.8734, longitude: 2.3335 },
];

test('URL de carte intégrée', () => {
    assert.strictEqual(C.urlCarte(48.8734, 2.3335, 15), 'https://maps.google.com/maps?q=48.8734,2.3335&z=15&output=embed');
});

test('URL d\'itinéraire', () => {
    assert.strictEqual(C.urlItineraire(43.2965, 5.3698), 'https://www.google.com/maps/dir/?api=1&destination=43.2965,5.3698');
});

test('distance Paris – Lyon d\'environ 390 km', () => {
    const d = C.distanceKm(48.8566, 2.3522, 45.764, 4.8357);
    assert.ok(d > 385 && d < 400, 'distance obtenue : ' + d);
});

test('le showroom le plus proche d\'Aix-en-Provence est Marseille', () => {
    const r = C.plusProche({ lat: 43.5297, lng: 5.4474 }, showrooms);
    assert.strictEqual(r.showroom.ville, 'Marseille');
    assert.ok(r.distance < 30);
});

test('le zoom reste entre 5 et 20', () => {
    assert.deepStrictEqual([C.bornerZoom(25), C.bornerZoom(2), C.bornerZoom(12)], [20, 5, 12]);
});
```

- [ ] **Step 2 : Lancer les tests pour vérifier qu'ils échouent**

Run : `php vendor/bin/phpunit tests/feature/ShowroomsPageTest.php` puis `node --test tests/js/`
Expected : FAIL.

- [ ] **Step 3 : Route et contrôleur**

Ajouter à `app/Config/Routes.php`, bloc « Pages vitrine » :

```php
$routes->get('showrooms', 'Pages::showrooms');
```

Dans `app/Controllers/Pages.php` :

```php
    public function showrooms(): string
    {
        return view('pages/showrooms', [
            'titre'     => 'Nos showrooms',
            'showrooms' => model(ShowroomModel::class)->orderBy('ville')->findAll(),
        ]);
    }
```

- [ ] **Step 4 : Vue**

`app/Views/pages/showrooms.php` :

```php
<?php
$premier = $showrooms[0] ?? null;
$urlCarte = static fn (array $s): string => 'https://maps.google.com/maps?q=' . (float) $s['latitude'] . ',' . (float) $s['longitude'] . '&z=15&output=embed';
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Nos showrooms</h1>
        <p>Venez essayer nos robots à Paris, Lyon et Marseille.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="showrooms" id="showrooms" data-api="<?= site_url('api/showrooms') ?>">
                    <button type="button" class="btn btn-robotix w-100 mb-2" id="btn-proche">📍 Showroom le plus proche</button>
                    <p class="small text-secondary" id="message-geo" aria-live="polite">Votre position n'est utilisée que dans votre navigateur.</p>
                    <ul class="showrooms__liste" id="liste-showrooms">
                        <?php foreach ($showrooms as $showroom): ?>
                            <li><?= esc($showroom['nom']) ?> — <?= esc($showroom['adresse']) ?>, <?= esc($showroom['codePostal']) ?> <?= esc($showroom['ville']) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="carte">
                    <?php if ($premier !== null): ?>
                        <iframe id="carte-google" class="carte__iframe" title="Carte Google Maps du showroom sélectionné"
                                src="<?= esc($urlCarte($premier)) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    <?php endif ?>
                    <div class="carte__zoom" role="group" aria-label="Zoom de la carte">
                        <button type="button" class="btn btn-light" id="zoom-plus" aria-label="Zoomer">+</button>
                        <button type="button" class="btn btn-light" id="zoom-moins" aria-label="Dézoomer">−</button>
                    </div>
                </div>
                <div class="carte__info" id="info-showroom" aria-live="polite">
                    <?php if ($premier !== null): ?>
                        <h2 class="h5"><?= esc($premier['nom']) ?></h2>
                        <p class="mb-0"><?= esc($premier['adresse']) ?>, <?= esc($premier['codePostal']) ?> <?= esc($premier['ville']) ?></p>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/carte.js') ?>"></script>
<?= $this->endSection() ?>
```

- [ ] **Step 5 : Script de la carte**

`public/js/carte.js` :

```js
/* Robotix — gestion de la carte Google Maps des showrooms */
(function (racine) {
    'use strict';

    const ZOOM_MIN = 5;
    const ZOOM_MAX = 20;

    const urlCarte = (lat, lng, zoom) => 'https://maps.google.com/maps?q=' + lat + ',' + lng + '&z=' + zoom + '&output=embed';
    const urlItineraire = (lat, lng) => 'https://www.google.com/maps/dir/?api=1&destination=' + lat + ',' + lng;
    const bornerZoom = (z) => Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, Math.round(z)));

    /** Distance à vol d'oiseau en km (formule de haversine). */
    function distanceKm(lat1, lng1, lat2, lng2) {
        const rad = (deg) => (deg * Math.PI) / 180;
        const dLat = rad(lat2 - lat1);
        const dLng = rad(lng2 - lng1);
        const a = Math.sin(dLat / 2) ** 2 + Math.cos(rad(lat1)) * Math.cos(rad(lat2)) * Math.sin(dLng / 2) ** 2;
        return 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function plusProche(position, showrooms) {
        return showrooms
            .map((s) => ({ showroom: s, distance: distanceKm(position.lat, position.lng, s.latitude, s.longitude) }))
            .sort((a, b) => a.distance - b.distance)[0];
    }

    const api = { urlCarte, urlItineraire, distanceKm, plusProche, bornerZoom };
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
        return;
    }
    racine.Carte = api;

    const conteneur = document.getElementById('showrooms');
    const iframe = document.getElementById('carte-google');
    if (!conteneur || !iframe) return;

    const liste = document.getElementById('liste-showrooms');
    const info = document.getElementById('info-showroom');
    const messageGeo = document.getElementById('message-geo');
    const etat = { showrooms: [], courant: null, zoom: 15, distances: {} };

    function element(balise, classe, texte) {
        const el = document.createElement(balise);
        if (classe) el.className = classe;
        if (texte !== undefined) el.textContent = texte;
        return el;
    }

    function rafraichirCarte() {
        const s = etat.courant;
        iframe.src = urlCarte(s.latitude, s.longitude, etat.zoom);
    }

    function selectionner(showroom) {
        etat.courant = showroom;
        rafraichirCarte();
        liste.querySelectorAll('button').forEach((b) => b.classList.toggle('active', Number(b.dataset.id) === showroom.id));

        const lien = element('a', 'btn btn-contour btn-sm mt-2', 'Itinéraire');
        lien.href = urlItineraire(showroom.latitude, showroom.longitude);
        lien.target = '_blank';
        lien.rel = 'noopener';
        info.replaceChildren(
            element('h2', 'h5', showroom.nom),
            element('p', 'mb-1', showroom.adresse + ', ' + showroom.codePostal + ' ' + showroom.ville),
            element('p', 'mb-1', '☎ ' + (showroom.telephone || '—') + ' · ' + (showroom.horaires || '')),
            lien,
        );
    }

    function dessinerListe() {
        liste.replaceChildren();
        etat.showrooms.forEach((s) => {
            const bouton = element('button', 'showrooms__item');
            bouton.type = 'button';
            bouton.dataset.id = s.id;
            bouton.append(element('strong', '', s.nom), element('span', 'd-block small', s.adresse + ', ' + s.ville));
            if (etat.distances[s.id] !== undefined) {
                bouton.append(element('span', 'badge text-bg-info', Math.round(etat.distances[s.id]) + ' km'));
            }
            bouton.addEventListener('click', () => selectionner(s));
            const item = document.createElement('li');
            item.append(bouton);
            liste.append(item);
        });
        if (etat.courant) selectionner(etat.courant);
    }

    async function charger() {
        try {
            const reponse = await fetch(conteneur.dataset.api);
            if (!reponse.ok) throw new Error('HTTP ' + reponse.status);
            etat.showrooms = await reponse.json();
            etat.courant = etat.showrooms[0] || null;
            dessinerListe();
        } catch (e) {
            messageGeo.textContent = 'La liste interactive est indisponible ; la carte reste consultable.';
        }
    }

    document.getElementById('zoom-plus').addEventListener('click', () => { etat.zoom = bornerZoom(etat.zoom + 1); if (etat.courant) rafraichirCarte(); });
    document.getElementById('zoom-moins').addEventListener('click', () => { etat.zoom = bornerZoom(etat.zoom - 1); if (etat.courant) rafraichirCarte(); });

    document.getElementById('btn-proche').addEventListener('click', () => {
        if (!('geolocation' in navigator)) {
            messageGeo.textContent = 'Votre navigateur ne permet pas la géolocalisation.';
            return;
        }
        messageGeo.textContent = 'Recherche de votre position…';
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const ici = { lat: position.coords.latitude, lng: position.coords.longitude };
                etat.showrooms.forEach((s) => { etat.distances[s.id] = distanceKm(ici.lat, ici.lng, s.latitude, s.longitude); });
                const resultat = plusProche(ici, etat.showrooms);
                etat.courant = resultat.showroom;
                dessinerListe();
                messageGeo.textContent = 'Le plus proche : ' + resultat.showroom.nom + ' (' + Math.round(resultat.distance) + ' km).';
            },
            () => { messageGeo.textContent = 'Position refusée ou indisponible : choisissez un showroom dans la liste.'; },
            { timeout: 10000 },
        );
    });

    charger();
})(this);
```

- [ ] **Step 6 : Styles (ajout à la fin de `robotix.css`)**

```css
/* ---------- Showrooms et carte ---------- */
.showrooms, .carte__info { background: #fff; border-radius: var(--rbx-rayon); padding: 1.5rem; box-shadow: var(--rbx-ombre); }
.showrooms__liste { list-style: none; padding: 0; margin: 0; display: grid; gap: .6rem; }
.showrooms__item {
    width: 100%; text-align: left; padding: .9rem 1rem; border: 1px solid #e2e8f0; border-radius: .8rem;
    background: var(--rbx-clair); transition: border-color .2s, background .2s;
}
.showrooms__item:hover, .showrooms__item.active { border-color: var(--rbx-cyan); background: #fff; }
.showrooms__item.active { box-shadow: inset 4px 0 0 var(--rbx-cyan); }
.carte { position: relative; border-radius: var(--rbx-rayon); overflow: hidden; box-shadow: var(--rbx-ombre); }
.carte__iframe { display: block; width: 100%; height: clamp(300px, 55vh, 480px); border: 0; }
.carte__zoom { position: absolute; top: .8rem; right: .8rem; display: grid; gap: .3rem; }
.carte__zoom .btn { width: 2.4rem; height: 2.4rem; font-weight: 700; box-shadow: 0 2px 8px rgba(0, 0, 0, .25); }
.carte__info { margin-top: 1rem; }
```

- [ ] **Step 7 : Lancer les tests pour vérifier qu'ils passent**

Run : `php vendor/bin/phpunit tests/feature/ShowroomsPageTest.php` puis `node --test tests/js/`
Expected : PASS (2 tests PHP ; 22 tests JS au total).

- [ ] **Step 8 : Vérification dans le navigateur**

Sur `/showrooms` : la carte de Lyon s'affiche ; un clic sur Marseille recentre la carte et met à jour l'adresse ; « + » et « − » zooment ; « Showroom le plus proche » demande l'autorisation, puis affiche les distances ; le lien « Itinéraire » ouvre Google Maps.

- [ ] **Step 9 : Point de contrôle** — suites PHP et JS vertes.

---

### Task 11 : Contrôle des champs (validation.js) et formulaire de contact

**Files :**
- Create : `public/js/validation.js`
- Create : `app/Models/MessageContactModel.php`
- Create : `app/Controllers/Contact.php`
- Create : `app/Views/pages/contact.php`
- Modify : `app/Config/Routes.php`, `public/css/robotix.css`
- Test : `tests/js/validation.test.js`, `tests/feature/ContactTest.php`

**Interfaces :**
- Consumes : `ProduitModel::pourCalculateur()` (liste des robots).
- Produces : dans `validation.js`, `Validation.REGLES` (`nom`, `email`, `telephone`, `codePostal`, `ville`, `adresse`, `motDePasse`, `message`) et `Validation.valider(regle, valeur): boolean`. Côté DOM : tout `<form data-valider novalidate>` est contrôlé ; chaque champ porte `data-regle="…"` (facultatif), éventuellement `data-identique="idAutreChamp"`, et un `<div class="invalid-feedback">` frère qui reçoit le message.
- Produces : `MessageContactModel::OBJETS` (`['demo' => 'Demande de démonstration', 'devis' => 'Demande de devis', 'sav' => 'Service après-vente', 'autre' => 'Autre question']`).
- Produces : les regex PHP, réutilisées par la tâche 12, notées dans `App\Controllers\Contact::MOTIFS` : `nom`, `telephone`.

- [ ] **Step 1 : Écrire les tests (échouent)**

`tests/js/validation.test.js` :

```js
const test = require('node:test');
const assert = require('node:assert');
const { valider } = require('../../public/js/validation.js');

const cas = {
    nom: [['Hélène', true], ["N'Diaye-Martin", true], ['A', false], ['R2D2', false]],
    email: [['camille@exemple.fr', true], ['camille@exemple', false], ['camille exemple.fr', false]],
    telephone: [['0612345678', true], ['06 12 34 56 78', true], ['06.12.34.56.78', true], ['0012345678', false], ['06123', false]],
    codePostal: [['69002', true], ['6900', false], ['69 002', false]],
    motDePasse: [['Robotix2026!', true], ['robotix2026', false], ['ROBOTIX2026', false], ['Robotix', false], ['Rob1', false]],
    message: [['Bonjour, je voudrais une démonstration.', true], ['Trop court', false]],
};

for (const [regle, exemples] of Object.entries(cas)) {
    test('règle ' + regle, () => {
        for (const [valeur, attendu] of exemples) {
            assert.strictEqual(valider(regle, valeur), attendu, regle + ' : « ' + valeur + ' »');
        }
    });
}

test('une règle inconnue ne bloque pas', () => {
    assert.strictEqual(valider('inexistante', 'x'), true);
});
```

`tests/feature/ContactTest.php` :

```php
<?php

use Tests\Support\RobotixTestCase;

final class ContactTest extends RobotixTestCase
{
    private function donneesValides(): array
    {
        return [
            'nom' => 'Hélène Châtelet', 'email' => 'helene@exemple.fr', 'telephone' => '06 12 34 56 78',
            'objet' => 'demo', 'idProduit' => (string) $this->idProduit('RBX-UNI-G1'),
            'message' => 'Bonjour, je souhaite voir le G1 au showroom de Lyon.', 'rgpd' => '1',
        ];
    }

    private function nombreMessages(): int
    {
        return $this->db->table('MessageContact')->countAllResults();
    }

    public function testLeFormulaireContientLesControlesHtml5(): void
    {
        $resultat = $this->get('contact');

        $resultat->assertOK();
        $resultat->assertSee('data-valider');
        $resultat->assertSee('type="email"');
        $resultat->assertSee('pattern="0[1-9](?:[ .\-]?\d{2}){4}"');
        $resultat->assertSee('data-regle="message"');
        $resultat->assertSee('js/validation.js');
    }

    public function testObjetEtRobotPreselectionnes(): void
    {
        $id = $this->idProduit('RBX-FIG-02');
        $resultat = $this->get('contact', ['objet' => 'devis', 'robot' => $id]);

        $resultat->assertSee('<option value="devis" selected>');
        $resultat->assertSee('<option value="' . $id . '" selected>');
    }

    public function testUnMessageValideEstEnregistre(): void
    {
        $avant = $this->nombreMessages();

        $resultat = $this->post('contact', $this->donneesValides());

        $resultat->assertRedirect();
        $resultat->assertSessionHas('succes');
        $this->assertSame($avant + 1, $this->nombreMessages());
        $message = $this->db->table('MessageContact')->where('email', 'helene@exemple.fr')->get()->getRowArray();
        $this->assertSame('Hélène Châtelet', $message['nom']);
    }

    public function testEmailInvalideRefuse(): void
    {
        $avant = $this->nombreMessages();

        $resultat = $this->post('contact', ['email' => 'pas-un-email'] + $this->donneesValides());

        $resultat->assertRedirect();
        $resultat->assertSessionHas('erreur');
        $this->assertSame($avant, $this->nombreMessages());
    }

    public function testConsentementObligatoire(): void
    {
        $donnees = $this->donneesValides();
        unset($donnees['rgpd']);

        $this->post('contact', $donnees)->assertSessionHas('erreur');
    }

    public function testObjetHorsListeRefuse(): void
    {
        $this->post('contact', ['objet' => 'piratage'] + $this->donneesValides())->assertSessionHas('erreur');
    }
}
```

- [ ] **Step 2 : Lancer les tests pour vérifier qu'ils échouent**

Run : `node --test tests/js/` puis `php vendor/bin/phpunit tests/feature/ContactTest.php`
Expected : FAIL.

- [ ] **Step 3 : Script de validation**

`public/js/validation.js` :

```js
/* Robotix — contrôle des formulaires : attributs HTML5 (pattern, required, type) + expressions régulières */
(function (racine) {
    'use strict';

    const LETTRES = "A-Za-zÀ-ÖØ-öø-ÿ' -";

    const REGLES = {
        nom: new RegExp('^[' + LETTRES + ']{2,50}$'),
        ville: new RegExp('^[' + LETTRES + ']{2,80}$'),
        email: /^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/,
        telephone: /^0[1-9](?:[ .-]?\d{2}){4}$/,
        codePostal: /^\d{5}$/,
        adresse: /^.{5,120}$/,
        motDePasse: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/,
        message: /^[\s\S]{20,2000}$/,
    };

    function valider(regle, valeur) {
        const motif = REGLES[regle];
        return motif ? motif.test(String(valeur).trim()) : true;
    }

    const api = { REGLES, valider };
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
        return;
    }
    racine.Validation = api;

    function verifierChamp(champ) {
        const valeur = champ.type === 'checkbox' ? champ.checked : champ.value;
        let valide = champ.checkValidity(); // required, type, pattern, minlength… (HTML5)

        if (valide && champ.dataset.regle && champ.value !== '') {
            valide = valider(champ.dataset.regle, valeur); // expression régulière JS
        }
        if (valide && champ.dataset.identique) {
            valide = champ.value === document.getElementById(champ.dataset.identique).value;
        }

        champ.classList.toggle('is-invalid', !valide);
        champ.classList.toggle('is-valid', valide && champ.value !== '');
        champ.setAttribute('aria-invalid', String(!valide));
        return valide;
    }

    document.querySelectorAll('form[data-valider]').forEach((formulaire) => {
        const champs = Array.from(formulaire.querySelectorAll('input, select, textarea'))
            .filter((c) => c.type !== 'hidden' && c.type !== 'submit');

        champs.forEach((champ) => {
            // Contrôle en direct, une fois que l'utilisateur a quitté le champ une première fois
            champ.addEventListener('blur', () => { champ.dataset.touche = '1'; verifierChamp(champ); });
            champ.addEventListener('input', () => { if (champ.dataset.touche) verifierChamp(champ); });
            champ.addEventListener('change', () => verifierChamp(champ));
        });

        formulaire.addEventListener('submit', (evenement) => {
            const invalides = champs.filter((champ) => !verifierChamp(champ));
            if (invalides.length > 0) {
                evenement.preventDefault();
                invalides[0].focus();
            }
        });
    });
})(this);
```

- [ ] **Step 4 : Modèle et contrôleur**

`app/Models/MessageContactModel.php` :

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageContactModel extends Model
{
    public const OBJETS = [
        'demo'  => 'Demande de démonstration',
        'devis' => 'Demande de devis',
        'sav'   => 'Service après-vente',
        'autre' => 'Autre question',
    ];

    protected $table         = 'MessageContact';
    protected $primaryKey    = 'idMessage';
    protected $returnType    = 'array';
    protected $allowedFields = ['nom', 'email', 'telephone', 'objet', 'idProduit', 'message', 'traite'];
}
```

Ajouter à `app/Config/Routes.php`, bloc « Pages vitrine » :

```php
$routes->get('contact', 'Contact::index');
$routes->post('contact', 'Contact::envoyer');
```

`app/Controllers/Contact.php` :

```php
<?php

namespace App\Controllers;

use App\Models\MessageContactModel;
use App\Models\ProduitModel;
use CodeIgniter\HTTP\RedirectResponse;

class Contact extends BaseController
{
    /** Mêmes contrôles que validation.js, répétés côté serveur (le JavaScript peut être désactivé). */
    public const MOTIFS = [
        'nom'       => "/^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,50}$/u",
        'telephone' => '/^0[1-9](?:[ .-]?\d{2}){4}$/',
    ];

    public function index(): string
    {
        return view('pages/contact', [
            'titre'       => 'Contact',
            'robots'      => model(ProduitModel::class)->pourCalculateur(),
            'objets'      => MessageContactModel::OBJETS,
            'objetChoisi' => (string) $this->request->getGet('objet'),
            'robotChoisi' => (int) $this->request->getGet('robot'),
        ]);
    }

    public function envoyer(): RedirectResponse
    {
        $regles = [
            'nom'       => ['label' => 'Nom', 'rules' => ['required', 'regex_match[' . self::MOTIFS['nom'] . ']']],
            'email'     => ['label' => 'E-mail', 'rules' => ['required', 'valid_email', 'max_length[150]']],
            'telephone' => ['label' => 'Téléphone', 'rules' => ['permit_empty', 'regex_match[' . self::MOTIFS['telephone'] . ']']],
            'objet'     => ['label' => 'Objet', 'rules' => ['required', 'in_list[' . implode(',', array_keys(MessageContactModel::OBJETS)) . ']']],
            'idProduit' => ['label' => 'Robot', 'rules' => ['permit_empty', 'is_natural_no_zero', 'is_not_unique[Produit.idProduit]']],
            'message'   => ['label' => 'Message', 'rules' => ['required', 'min_length[20]', 'max_length[2000]']],
            'rgpd'      => ['label' => 'Consentement', 'rules' => ['required'], 'errors' => ['required' => 'Merci d\'accepter le traitement de vos données.']],
        ];

        if (! $this->validate($regles)) {
            return redirect()->back()->withInput()->with('erreur', 'Le formulaire contient des erreurs : vérifiez les champs signalés.');
        }

        $donnees = $this->validator->getValidated();

        model(MessageContactModel::class)->insert([
            'nom'       => trim($donnees['nom']),
            'email'     => strtolower(trim($donnees['email'])),
            'telephone' => ($donnees['telephone'] ?? '') !== '' ? $donnees['telephone'] : null,
            'objet'     => $donnees['objet'],
            'idProduit' => ($donnees['idProduit'] ?? '') !== '' ? (int) $donnees['idProduit'] : null,
            'message'   => trim($donnees['message']),
        ]);

        return redirect()->to(site_url('contact'))
            ->with('succes', 'Merci ! Votre message a bien été envoyé, nous vous répondrons sous 48 heures.');
    }
}
```

- [ ] **Step 5 : Vue**

`app/Views/pages/contact.php` :

```php
<?php
$objetActuel = old('objet', $objetChoisi);
$robotActuel = (int) old('idProduit', (string) $robotChoisi);
$classe = static fn (string $champ): string => validation_show_error($champ) !== '' ? ' is-invalid' : '';
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Contact</h1>
        <p>Une démonstration, un devis, une question ? Nous vous répondons sous 48 heures.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <form class="formulaire" action="<?= site_url('contact') ?>" method="post" data-valider novalidate>
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="nom">Nom et prénom *</label>
                            <input class="form-control<?= $classe('nom') ?>" type="text" id="nom" name="nom" value="<?= old('nom') ?>"
                                   required minlength="2" maxlength="50" pattern="[A-Za-zÀ-ÖØ-öø-ÿ' \-]{2,50}" data-regle="nom" autocomplete="name">
                            <div class="invalid-feedback"><?= validation_show_error('nom') ?: 'Lettres, espaces, apostrophes et tirets uniquement (2 à 50 caractères).' ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">E-mail *</label>
                            <input class="form-control<?= $classe('email') ?>" type="email" id="email" name="email" value="<?= old('email') ?>"
                                   required maxlength="150" data-regle="email" autocomplete="email">
                            <div class="invalid-feedback"><?= validation_show_error('email') ?: 'Saisissez une adresse e-mail valide (ex. : nom@exemple.fr).' ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="telephone">Téléphone</label>
                            <input class="form-control<?= $classe('telephone') ?>" type="tel" id="telephone" name="telephone" value="<?= old('telephone') ?>"
                                   pattern="0[1-9](?:[ .\-]?\d{2}){4}" data-regle="telephone" autocomplete="tel">
                            <div class="invalid-feedback"><?= validation_show_error('telephone') ?: 'Numéro français à 10 chiffres (ex. : 06 12 34 56 78).' ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="objet">Objet *</label>
                            <select class="form-select<?= $classe('objet') ?>" id="objet" name="objet" required>
                                <option value="">Choisissez…</option>
                                <?php foreach ($objets as $code => $libelle): ?>
                                    <option value="<?= $code ?>"<?= $objetActuel === $code ? ' selected' : '' ?>><?= esc($libelle) ?></option>
                                <?php endforeach ?>
                            </select>
                            <div class="invalid-feedback"><?= validation_show_error('objet') ?: 'Choisissez l\'objet de votre demande.' ?></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="idProduit">Robot concerné</label>
                            <select class="form-select<?= $classe('idProduit') ?>" id="idProduit" name="idProduit">
                                <option value="">Aucun en particulier</option>
                                <?php foreach ($robots as $robot): ?>
                                    <option value="<?= $robot['idProduit'] ?>"<?= (int) $robot['idProduit'] === $robotActuel ? ' selected' : '' ?>><?= esc($robot['nom']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="message">Message *</label>
                            <textarea class="form-control<?= $classe('message') ?>" id="message" name="message" rows="6"
                                      required minlength="20" maxlength="2000" data-regle="message"><?= old('message') ?></textarea>
                            <div class="invalid-feedback"><?= validation_show_error('message') ?: 'Votre message doit contenir entre 20 et 2 000 caractères.' ?></div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input<?= $classe('rgpd') ?>" type="checkbox" id="rgpd" name="rgpd" value="1" required>
                                <label class="form-check-label" for="rgpd">J'accepte que Robotix utilise ces informations pour me répondre. *</label>
                                <div class="invalid-feedback"><?= validation_show_error('rgpd') ?: 'Merci d\'accepter le traitement de vos données.' ?></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-robotix" type="submit">Envoyer</button>
                            <p class="small text-secondary mt-2 mb-0">* Champs obligatoires</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-4">
                <aside class="univers">
                    <h2 class="h5">Nous rencontrer</h2>
                    <p>Nos conseillers vous accueillent du mardi au samedi, de 10 h à 19 h, à Paris, Lyon et Marseille.</p>
                    <a href="<?= site_url('showrooms') ?>">Trouver un showroom →</a>
                </aside>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/validation.js') ?>"></script>
<?= $this->endSection() ?>
```

- [ ] **Step 6 : Styles (ajout à la fin de `robotix.css`)**

```css
/* ---------- Formulaires ---------- */
.formulaire { background: #fff; border-radius: var(--rbx-rayon); padding: 2rem; box-shadow: var(--rbx-ombre); }
.formulaire .form-control:focus, .formulaire .form-select:focus {
    border-color: var(--rbx-cyan); box-shadow: 0 0 0 .2rem rgba(0, 212, 255, .25);
}
.formulaire--etroit { max-width: 640px; margin: 0 auto; }
```

- [ ] **Step 7 : Lancer les tests pour vérifier qu'ils passent**

Run : `node --test tests/js/` puis `php vendor/bin/phpunit tests/feature/ContactTest.php`
Expected : PASS (29 tests JS au total ; 6 tests PHP).

- [ ] **Step 8 : Vérification dans le navigateur**

Sur `/contact` : saisir « 0612 » dans Téléphone puis quitter le champ → message rouge ; soumettre vide → focus sur « Nom » ; désactiver JavaScript (DevTools > Ctrl+Maj+P > « Disable JavaScript ») puis soumettre un e-mail invalide → le serveur renvoie l'erreur sous le champ.

- [ ] **Step 9 : Point de contrôle** — suites PHP et JS vertes.

---

### Task 12 : Inscription, connexion, déconnexion et filtres d'accès

**Files :**
- Create : `app/Models/UtilisateurModel.php`, `app/Models/ClientModel.php`, `app/Models/AdresseModel.php`
- Create : `app/Controllers/Compte/Auth.php`
- Create : `app/Filters/AuthFilter.php`, `app/Filters/AdminFilter.php`
- Create : `app/Views/compte/connexion.php`, `app/Views/compte/inscription.php`
- Modify : `app/Config/Filters.php` (alias), `app/Config/Routes.php`
- Test : `tests/feature/AuthTest.php`

**Interfaces :**
- Consumes : `Contact::MOTIFS` (tâche 11), `validation.js` (tâche 11).
- Produces : `UtilisateurModel::trouverParEmail(string $email): ?array`, `UtilisateurModel::role(int $id): string` (`admin` | `redacteur` | `client` | `visiteur`).
- Produces : la session `idUtilisateur` (int), `nom`, `prenom` et `role` ; les alias de filtres `auth` et `admin`. Un visiteur non connecté est redirigé vers `compte/connexion`, et l'URL demandée est gardée dans la session sous `redirection`.

- [ ] **Step 1 : Écrire le test (échoue)**

`tests/feature/AuthTest.php` :

```php
<?php

use Tests\Support\RobotixTestCase;

final class AuthTest extends RobotixTestCase
{
    private function inscription(array $surcharge = []): array
    {
        return $surcharge + [
            'nom' => 'Châtelet', 'prenom' => 'Hélène', 'email' => 'helene.chatelet@exemple.fr',
            'telephone' => '06 98 76 54 32', 'adresse' => '5 place Bellecour', 'codePostal' => '69002',
            'ville' => 'Lyon', 'motDePasse' => 'Robotix2026!', 'confirmation' => 'Robotix2026!',
        ];
    }

    public function testLesPagesDeCompteSAffichent(): void
    {
        $this->get('compte/connexion')->assertSee('Connexion', 'h1');
        $inscription = $this->get('compte/inscription');
        $inscription->assertSee('data-valider');
        $inscription->assertSee('data-identique="motDePasse"');
        $inscription->assertSee('pattern="\d{5}"');
    }

    public function testInscriptionCreeUtilisateurClientEtAdresse(): void
    {
        $resultat = $this->post('compte/inscription', $this->inscription());

        $resultat->assertRedirect();
        $resultat->assertSessionHas('role', 'client');
        $id = $this->idUtilisateur('helene.chatelet@exemple.fr');
        $this->assertGreaterThan(0, $id);

        $utilisateur = $this->db->table('Utilisateur')->where('idUtilisateur', $id)->get()->getRowArray();
        $this->assertSame('Hélène', $utilisateur['prenom']); // accents conservés
        $this->assertTrue(password_verify('Robotix2026!', $utilisateur['motDePasse']));
        $this->assertSame(1, $this->db->table('Client')->where('idUtilisateur', $id)->countAllResults());
        $this->assertSame('Lyon', $this->db->table('Adresse')->where('idUtilisateur', $id)->get()->getRow('ville'));
    }

    public function testEmailDejaUtiliseRefuseMemeEnMajuscules(): void
    {
        $resultat = $this->post('compte/inscription', $this->inscription(['email' => 'CLIENT@Robotix.test']));

        $resultat->assertSessionHas('erreur');
        $resultat->assertSessionMissing('idUtilisateur');
    }

    public function testMotDePasseFaibleRefuse(): void
    {
        $this->post('compte/inscription', $this->inscription(['motDePasse' => 'robotix', 'confirmation' => 'robotix']))
            ->assertSessionHas('erreur');
        $this->assertSame(0, $this->idUtilisateur('helene.chatelet@exemple.fr'));
    }

    public function testConfirmationDifferenteRefusee(): void
    {
        $this->post('compte/inscription', $this->inscription(['confirmation' => 'Autre2026!']))
            ->assertSessionHas('erreur');
    }

    public function testConnexionAdmin(): void
    {
        $resultat = $this->post('compte/connexion', ['email' => 'admin@robotix.test', 'motDePasse' => 'Robotix2026!']);

        $resultat->assertRedirect();
        $resultat->assertSessionHas('role', 'admin');
        $resultat->assertSessionHas('prenom', 'Hugo');
    }

    public function testConnexionInsensibleALaCasseDeLEmail(): void
    {
        $this->post('compte/connexion', ['email' => ' Client@Robotix.TEST ', 'motDePasse' => 'Robotix2026!'])
            ->assertSessionHas('role', 'client');
    }

    public function testMauvaisMotDePasse(): void
    {
        $resultat = $this->post('compte/connexion', ['email' => 'admin@robotix.test', 'motDePasse' => 'mauvais']);

        $resultat->assertSessionHas('erreur');
        $resultat->assertSessionMissing('idUtilisateur');
    }

    public function testCompteDesactiveRefuse(): void
    {
        $this->db->table('Utilisateur')->where('email', 'client@robotix.test')->update(['actif' => 0]);

        $this->post('compte/connexion', ['email' => 'client@robotix.test', 'motDePasse' => 'Robotix2026!'])
            ->assertSessionMissing('idUtilisateur');
    }

    public function testDeconnexion(): void
    {
        $resultat = $this->withSession($this->sessionDe('client@robotix.test'))->post('compte/deconnexion');

        $resultat->assertRedirect();
        $resultat->assertSessionMissing('idUtilisateur');
    }
}
```

- [ ] **Step 2 : Lancer le test pour vérifier qu'il échoue**

Run : `php vendor/bin/phpunit tests/feature/AuthTest.php`
Expected : FAIL (route `compte/connexion` inconnue).

- [ ] **Step 3 : Modèles**

`app/Models/UtilisateurModel.php` :

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class UtilisateurModel extends Model
{
    protected $table         = 'Utilisateur';
    protected $primaryKey    = 'idUtilisateur';
    protected $returnType    = 'array';
    protected $allowedFields = ['nom', 'prenom', 'email', 'motDePasse', 'actif'];

    public function trouverParEmail(string $email): ?array
    {
        return $this->where('email', strtolower(trim($email)))->first();
    }

    /** Le rôle dépend de la table spécialisée dans laquelle figure l'utilisateur. */
    public function role(int $id): string
    {
        foreach (['Administrateur' => 'admin', 'Redacteur' => 'redacteur', 'Client' => 'client'] as $table => $role) {
            if ($this->db->table($table)->where('idUtilisateur', $id)->countAllResults() > 0) {
                return $role;
            }
        }

        return 'visiteur';
    }
}
```

`app/Models/ClientModel.php` :

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table            = 'Client';
    protected $primaryKey       = 'idUtilisateur';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = ['idUtilisateur', 'telephone'];
}
```

`app/Models/AdresseModel.php` :

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class AdresseModel extends Model
{
    protected $table         = 'Adresse';
    protected $primaryKey    = 'idAdresse';
    protected $returnType    = 'array';
    protected $allowedFields = ['libelle', 'ligne1', 'ligne2', 'codePostal', 'ville', 'pays', 'idUtilisateur'];
}
```

- [ ] **Step 4 : Filtres**

`app/Filters/AuthFilter.php` :

```php
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
```

`app/Filters/AdminFilter.php` :

```php
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
```

Dans `app/Config/Filters.php`, ajouter les `use` et les alias :

```php
use App\Filters\AdminFilter;
use App\Filters\AuthFilter;
```

```php
        'auth'  => AuthFilter::class,
        'admin' => AdminFilter::class,
```

- [ ] **Step 5 : Routes**

Ajouter à `app/Config/Routes.php` :

```php
// Compte
$routes->group('compte', static function ($routes) {
    $routes->get('connexion', 'Compte\Auth::connexion');
    $routes->post('connexion', 'Compte\Auth::seConnecter');
    $routes->get('inscription', 'Compte\Auth::inscription');
    $routes->post('inscription', 'Compte\Auth::inscrire');
    $routes->post('deconnexion', 'Compte\Auth::deconnexion');
});
```

- [ ] **Step 6 : Contrôleur**

`app/Controllers/Compte/Auth.php` :

```php
<?php

namespace App\Controllers\Compte;

use App\Controllers\BaseController;
use App\Controllers\Contact;
use App\Models\AdresseModel;
use App\Models\ClientModel;
use App\Models\UtilisateurModel;
use CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
    public function connexion(): string
    {
        return view('compte/connexion', ['titre' => 'Connexion']);
    }

    public function seConnecter(): RedirectResponse
    {
        $utilisateurs = model(UtilisateurModel::class);
        $utilisateur  = $utilisateurs->trouverParEmail((string) $this->request->getPost('email'));
        $motDePasse   = (string) $this->request->getPost('motDePasse');

        if ($utilisateur === null || ! (bool) $utilisateur['actif'] || ! password_verify($motDePasse, $utilisateur['motDePasse'])) {
            return redirect()->back()->withInput()->with('erreur', 'E-mail ou mot de passe incorrect.');
        }

        $this->ouvrirSession($utilisateur, $utilisateurs->role((int) $utilisateur['idUtilisateur']));

        $destination = session('redirection') ?? site_url('/');
        session()->remove('redirection');

        return redirect()->to($destination)->with('succes', 'Bonjour ' . $utilisateur['prenom'] . ' !');
    }

    public function inscription(): string
    {
        return view('compte/inscription', ['titre' => 'Créer un compte']);
    }

    public function inscrire(): RedirectResponse
    {
        // is_unique s'appuie sur la collation de SQL Server, insensible à la casse :
        // « CLIENT@Robotix.test » est donc reconnu comme doublon de « client@robotix.test ».
        $regles = [
            'nom'          => ['label' => 'Nom', 'rules' => ['required', 'regex_match[' . Contact::MOTIFS['nom'] . ']']],
            'prenom'       => ['label' => 'Prénom', 'rules' => ['required', 'regex_match[' . Contact::MOTIFS['nom'] . ']']],
            'email'        => ['label' => 'E-mail', 'rules' => ['required', 'valid_email', 'max_length[150]', 'is_unique[Utilisateur.email]'],
                               'errors' => ['is_unique' => 'Un compte existe déjà avec cet e-mail.']],
            'telephone'    => ['label' => 'Téléphone', 'rules' => ['required', 'regex_match[' . Contact::MOTIFS['telephone'] . ']']],
            'adresse'      => ['label' => 'Adresse', 'rules' => ['required', 'min_length[5]', 'max_length[120]']],
            'codePostal'   => ['label' => 'Code postal', 'rules' => ['required', 'regex_match[/^\d{5}$/]']],
            'ville'        => ['label' => 'Ville', 'rules' => ['required', 'regex_match[' . str_replace('{2,50}', '{2,80}', Contact::MOTIFS['nom']) . ']']],
            'motDePasse'   => ['label' => 'Mot de passe', 'rules' => ['required', 'regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/]'],
                               'errors' => ['regex_match' => '8 caractères minimum, avec une majuscule, une minuscule et un chiffre.']],
            'confirmation' => ['label' => 'Confirmation', 'rules' => ['required', 'matches[motDePasse]'],
                               'errors' => ['matches' => 'Les deux mots de passe sont différents.']],
        ];

        if (! $this->validate($regles)) {
            return redirect()->back()->withInput()->with('erreur', 'Merci de corriger les champs signalés.');
        }

        $d  = $this->validator->getValidated();
        $db = db_connect();
        $db->transStart();

        $id = (int) model(UtilisateurModel::class)->insert([
            'nom'        => trim($d['nom']),
            'prenom'     => trim($d['prenom']),
            'email'      => strtolower(trim($d['email'])),
            'motDePasse' => password_hash($d['motDePasse'], PASSWORD_DEFAULT),
            'actif'      => 1,
        ]);
        model(ClientModel::class)->insert(['idUtilisateur' => $id, 'telephone' => $d['telephone']]);
        model(AdresseModel::class)->insert([
            'libelle' => 'Domicile', 'ligne1' => trim($d['adresse']), 'codePostal' => $d['codePostal'],
            'ville' => trim($d['ville']), 'pays' => 'France', 'idUtilisateur' => $id,
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('erreur', 'L\'inscription a échoué, veuillez réessayer.');
        }

        $this->ouvrirSession(['idUtilisateur' => $id, 'nom' => trim($d['nom']), 'prenom' => trim($d['prenom'])], 'client');

        return redirect()->to(site_url('/'))->with('succes', 'Bienvenue chez Robotix, ' . trim($d['prenom']) . ' !');
    }

    public function deconnexion(): RedirectResponse
    {
        session()->remove(['idUtilisateur', 'nom', 'prenom', 'role']);
        session()->regenerate(true);

        return redirect()->to(site_url('/'))->with('succes', 'Vous êtes déconnecté. À bientôt !');
    }

    private function ouvrirSession(array $utilisateur, string $role): void
    {
        session()->regenerate(); // nouvel identifiant de session : protège contre la fixation de session
        session()->set([
            'idUtilisateur' => (int) $utilisateur['idUtilisateur'],
            'nom'           => $utilisateur['nom'],
            'prenom'        => $utilisateur['prenom'],
            'role'          => $role,
        ]);
    }
}
```

- [ ] **Step 7 : Vues**

`app/Views/compte/connexion.php` :

```php
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="section">
    <div class="container">
        <form class="formulaire formulaire--etroit" action="<?= site_url('compte/connexion') ?>" method="post" data-valider novalidate>
            <?= csrf_field() ?>
            <h1 class="h3 mb-4">Connexion</h1>
            <div class="mb-3">
                <label class="form-label" for="email">E-mail</label>
                <input class="form-control" type="email" id="email" name="email" value="<?= old('email') ?>" required data-regle="email" autocomplete="username">
                <div class="invalid-feedback">Saisissez votre adresse e-mail.</div>
            </div>
            <div class="mb-4">
                <label class="form-label" for="motDePasse">Mot de passe</label>
                <input class="form-control" type="password" id="motDePasse" name="motDePasse" required autocomplete="current-password">
                <div class="invalid-feedback">Saisissez votre mot de passe.</div>
            </div>
            <button class="btn btn-robotix w-100" type="submit">Se connecter</button>
            <p class="text-center mt-3 mb-0">Pas encore de compte ? <a href="<?= site_url('compte/inscription') ?>">Créer un compte</a></p>
        </form>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/validation.js') ?>"></script>
<?= $this->endSection() ?>
```

`app/Views/compte/inscription.php` :

```php
<?php
$classe = static fn (string $champ): string => validation_show_error($champ) !== '' ? ' is-invalid' : '';
$champ = static function (string $nom, string $libelle, string $attributs, string $aide) use ($classe): string {
    $erreur = validation_show_error($nom);

    return '<label class="form-label" for="' . $nom . '">' . $libelle . '</label>'
        . '<input class="form-control' . $classe($nom) . '" id="' . $nom . '" name="' . $nom . '" ' . $attributs . '>'
        . '<div class="invalid-feedback">' . ($erreur !== '' ? $erreur : esc($aide)) . '</div>';
};
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="section">
    <div class="container">
        <form class="formulaire formulaire--etroit" action="<?= site_url('compte/inscription') ?>" method="post" data-valider novalidate>
            <?= csrf_field() ?>
            <h1 class="h3 mb-1">Créer un compte</h1>
            <p class="text-secondary mb-4">Pour être recontacté, suivre vos demandes et commander vos robots.</p>
            <div class="row g-3">
                <div class="col-sm-6"><?= $champ('prenom', 'Prénom *', 'type="text" required maxlength="50" pattern="[A-Za-zÀ-ÖØ-öø-ÿ\' \-]{2,50}" data-regle="nom" autocomplete="given-name" value="' . old('prenom') . '"', 'Lettres uniquement (2 à 50 caractères).') ?></div>
                <div class="col-sm-6"><?= $champ('nom', 'Nom *', 'type="text" required maxlength="50" pattern="[A-Za-zÀ-ÖØ-öø-ÿ\' \-]{2,50}" data-regle="nom" autocomplete="family-name" value="' . old('nom') . '"', 'Lettres uniquement (2 à 50 caractères).') ?></div>
                <div class="col-sm-6"><?= $champ('email', 'E-mail *', 'type="email" required maxlength="150" data-regle="email" autocomplete="email" value="' . old('email') . '"', 'Adresse e-mail valide (ex. : nom@exemple.fr).') ?></div>
                <div class="col-sm-6"><?= $champ('telephone', 'Téléphone *', 'type="tel" required pattern="0[1-9](?:[ .\-]?\d{2}){4}" data-regle="telephone" autocomplete="tel" value="' . old('telephone') . '"', 'Numéro français à 10 chiffres.') ?></div>
                <div class="col-12"><?= $champ('adresse', 'Adresse *', 'type="text" required minlength="5" maxlength="120" data-regle="adresse" autocomplete="street-address" value="' . old('adresse') . '"', 'Numéro et nom de rue.') ?></div>
                <div class="col-sm-4"><?= $champ('codePostal', 'Code postal *', 'type="text" required inputmode="numeric" pattern="\d{5}" data-regle="codePostal" autocomplete="postal-code" value="' . old('codePostal') . '"', '5 chiffres.') ?></div>
                <div class="col-sm-8"><?= $champ('ville', 'Ville *', 'type="text" required maxlength="80" data-regle="ville" autocomplete="address-level2" value="' . old('ville') . '"', 'Nom de la ville.') ?></div>
                <div class="col-sm-6"><?= $champ('motDePasse', 'Mot de passe *', 'type="password" required minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}" data-regle="motDePasse" autocomplete="new-password"', '8 caractères minimum, avec une majuscule, une minuscule et un chiffre.') ?></div>
                <div class="col-sm-6"><?= $champ('confirmation', 'Confirmation *', 'type="password" required data-identique="motDePasse" autocomplete="new-password"', 'Les deux mots de passe doivent être identiques.') ?></div>
                <div class="col-12">
                    <button class="btn btn-robotix w-100" type="submit">Créer mon compte</button>
                    <p class="text-center mt-3 mb-0">Déjà client ? <a href="<?= site_url('compte/connexion') ?>">Se connecter</a></p>
                </div>
            </div>
        </form>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/validation.js') ?>"></script>
<?= $this->endSection() ?>
```

- [ ] **Step 8 : Lancer le test pour vérifier qu'il passe**

Run : `php vendor/bin/phpunit tests/feature/AuthTest.php`
Expected : PASS (10 tests). Si `testInscriptionCreeUtilisateurClientEtAdresse` échoue sur « Hélène » (lu « H?l?ne ») : la collation de la colonne ne gère pas l'UTF-8. Ajouter alors `'charset' => 'UTF-8'` dans la connexion par défaut, ou passer la colonne en `NVARCHAR`.

- [ ] **Step 9 : Vérification dans le navigateur**

Créer un compte : le prénom s'affiche dans le menu. Se déconnecter, puis se reconnecter avec `admin@robotix.test` : le menu propose « Gérer le planning ».

- [ ] **Step 10 : Point de contrôle** — `php vendor/bin/phpunit` : tout est vert.

---

### Task 13 : Gestion du planning (administration des événements)

**Files :**
- Create : `app/Libraries/Journaliseur.php`
- Create : `app/Controllers/Admin/Evenements.php`
- Create : `app/Views/admin/evenements/index.php`, `app/Views/admin/evenements/formulaire.php`
- Modify : `app/Config/Routes.php`
- Test : `tests/feature/AdminEvenementsTest.php`

**Interfaces :**
- Consumes : `EvenementModel` (`TYPES`, `tous()`, `chevauche()`), `ShowroomModel`, `ProduitModel::pourCalculateur()`, le filtre `admin` (tâche 12), `date_sql()`, `date_saisie()` et `date_fr()` (tâche 1), `data-confirm` (tâche 4).
- Produces : `Journaliseur::noter(string $typeAction, string $tableCible, int $idCible, string $description): void`. Il écrit dans `Journal` au nom de l'utilisateur connecté et ne fait rien si personne n'est connecté.
- Produces : les routes `admin/evenements` (GET liste, POST création), `admin/evenements/nouveau`, `admin/evenements/(:num)/modifier`, `admin/evenements/(:num)` (POST mise à jour) et `admin/evenements/(:num)/supprimer` (POST).

- [ ] **Step 1 : Écrire le test (échoue)**

`tests/feature/AdminEvenementsTest.php` :

```php
<?php

use Tests\Support\RobotixTestCase;

final class AdminEvenementsTest extends RobotixTestCase
{
    private function admin(): array
    {
        return $this->sessionDe('admin@robotix.test');
    }

    private function idShowroom(string $ville): int
    {
        return (int) $this->db->table('Showroom')->where('ville', $ville)->get()->getRow('idShowroom');
    }

    private function evenement(array $surcharge = []): array
    {
        return $surcharge + [
            'titre' => 'Soirée portes ouvertes', 'description' => 'Tous les robots en démonstration.',
            'type' => 'demo', 'dateDebut' => '2026-11-26T18:00', 'dateFin' => '2026-11-26T21:00',
            'idShowroom' => (string) $this->idShowroom('Marseille'), 'idProduit' => '',
        ];
    }

    private function nombre(): int
    {
        return $this->db->table('Evenement')->countAllResults();
    }

    public function testVisiteurRedirigeVersLaConnexion(): void
    {
        $resultat = $this->get('admin/evenements');

        $resultat->assertRedirect();
        $this->assertStringContainsString('compte/connexion', $resultat->getRedirectUrl());
    }

    public function testClientRefuse(): void
    {
        $resultat = $this->withSession($this->sessionDe('client@robotix.test'))->get('admin/evenements');

        $resultat->assertRedirect();
        $resultat->assertSessionHas('erreur', 'Accès réservé aux administrateurs.');
    }

    public function testAdminVoitLePlanning(): void
    {
        $resultat = $this->withSession($this->admin())->get('admin/evenements');

        $resultat->assertOK();
        $resultat->assertSee('Gestion du planning', 'h1');
        $resultat->assertSee('Démonstration Digit');
        $resultat->assertSee('data-confirm=');
    }

    public function testCreationValideEtJournalisee(): void
    {
        $avant = $this->nombre();

        $resultat = $this->withSession($this->admin())->post('admin/evenements', $this->evenement());

        $resultat->assertRedirect();
        $resultat->assertSessionHas('succes');
        $this->assertSame($avant + 1, $this->nombre());

        $cree = $this->db->table('Evenement')->where('titre', 'Soirée portes ouvertes')->get()->getRowArray();
        $this->assertStringStartsWith('2026-11-26 18:00', $cree['dateDebut']); // le 26 novembre, pas d'inversion jour/mois
        $this->assertNull($cree['idProduit']);
        $this->assertSame(1, $this->db->table('Journal')->where('tableCible', 'Evenement')->where('idCible', $cree['idEvenement'])->countAllResults());
    }

    public function testFinAvantDebutRefusee(): void
    {
        $avant = $this->nombre();

        $this->withSession($this->admin())
            ->post('admin/evenements', $this->evenement(['dateFin' => '2026-11-26T17:00']))
            ->assertSessionHas('erreur', 'La fin doit être postérieure au début.');
        $this->assertSame($avant, $this->nombre());
    }

    public function testChevauchementDansLeMemeShowroomRefuse(): void
    {
        // « Démonstration Digit » occupe Marseille le 25/11/2026 de 14 h à 17 h
        $this->withSession($this->admin())
            ->post('admin/evenements', $this->evenement(['dateDebut' => '2026-11-25T15:00', 'dateFin' => '2026-11-25T16:00']))
            ->assertSessionHas('erreur', 'Ce showroom a déjà un événement sur ce créneau.');
    }

    public function testMemeCreneauDansUnAutreShowroomAccepte(): void
    {
        $this->withSession($this->admin())
            ->post('admin/evenements', $this->evenement([
                'dateDebut' => '2026-11-25T15:00', 'dateFin' => '2026-11-25T16:00',
                'idShowroom' => (string) $this->idShowroom('Paris'),
            ]))
            ->assertSessionHas('succes');
    }

    public function testTypeInconnuRefuse(): void
    {
        $this->withSession($this->admin())
            ->post('admin/evenements', $this->evenement(['type' => 'concert']))
            ->assertSessionHas('erreur');
    }

    public function testModification(): void
    {
        $id = (int) $this->db->table('Evenement')->where('titre', 'Démonstration Digit')->get()->getRow('idEvenement');

        $resultat = $this->withSession($this->admin())->post('admin/evenements/' . $id, $this->evenement([
            'titre' => 'Démonstration Digit (complet)', 'dateDebut' => '2026-11-25T14:00', 'dateFin' => '2026-11-25T17:00',
        ]));

        $resultat->assertSessionHas('succes'); // ne se chevauche pas avec lui-même
        $this->assertSame('Démonstration Digit (complet)', $this->db->table('Evenement')->where('idEvenement', $id)->get()->getRow('titre'));
    }

    public function testSuppression(): void
    {
        $id = (int) $this->db->table('Evenement')->where('titre', 'Démonstration Digit')->get()->getRow('idEvenement');

        $this->withSession($this->admin())->post('admin/evenements/' . $id . '/supprimer')->assertSessionHas('succes');
        $this->assertSame(0, $this->db->table('Evenement')->where('idEvenement', $id)->countAllResults());
    }

    public function testLeTitreEstEchappeALAffichage(): void
    {
        $this->withSession($this->admin())->post('admin/evenements', $this->evenement(['titre' => '<script>alert(1)</script>']));

        $resultat = $this->withSession($this->admin())->get('admin/evenements');
        $resultat->assertDontSee('<script>alert(1)</script>');
        $resultat->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;');
    }

    public function testFormulaireDeModificationPrerempli(): void
    {
        $id = (int) $this->db->table('Evenement')->where('titre', 'Démonstration Digit')->get()->getRow('idEvenement');

        $this->withSession($this->admin())->get('admin/evenements/' . $id . '/modifier')
            ->assertSee('value="2026-11-25T14:00"');
    }
}
```

- [ ] **Step 2 : Lancer le test pour vérifier qu'il échoue**

Run : `php vendor/bin/phpunit tests/feature/AdminEvenementsTest.php`
Expected : FAIL (route `admin/evenements` inconnue).

- [ ] **Step 3 : Journal des actions**

`app/Libraries/Journaliseur.php` :

```php
<?php

namespace App\Libraries;

/**
 * Trace les actions d'administration dans la table Journal.
 */
final class Journaliseur
{
    public static function noter(string $typeAction, string $tableCible, int $idCible, string $description): void
    {
        $idUtilisateur = (int) session('idUtilisateur');

        if ($idUtilisateur === 0) {
            return;
        }

        db_connect()->table('Journal')->insert([
            'typeAction'    => mb_substr($typeAction, 0, 20),
            'tableCible'    => $tableCible,
            'idCible'       => $idCible,
            'description'   => mb_substr($description, 0, 500),
            'idUtilisateur' => $idUtilisateur,
        ]);
    }
}
```

- [ ] **Step 4 : Routes**

Ajouter à `app/Config/Routes.php` :

```php
// Administration (réservée au rôle admin)
$routes->group('admin', ['filter' => 'admin'], static function ($routes) {
    $routes->get('/', static fn () => redirect()->to(site_url('admin/evenements')));
    $routes->get('evenements', 'Admin\Evenements::index');
    $routes->get('evenements/nouveau', 'Admin\Evenements::nouveau');
    $routes->post('evenements', 'Admin\Evenements::creer');
    $routes->get('evenements/(:num)/modifier', 'Admin\Evenements::modifier/$1');
    $routes->post('evenements/(:num)', 'Admin\Evenements::mettreAJour/$1');
    $routes->post('evenements/(:num)/supprimer', 'Admin\Evenements::supprimer/$1');
});
```

- [ ] **Step 5 : Contrôleur**

`app/Controllers/Admin/Evenements.php` :

```php
<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Journaliseur;
use App\Models\EvenementModel;
use App\Models\ProduitModel;
use App\Models\ShowroomModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Gestion du planning : ajout, modification et suppression des événements.
 */
class Evenements extends BaseController
{
    public function index(): string
    {
        return view('admin/evenements/index', [
            'titre'      => 'Gestion du planning',
            'evenements' => model(EvenementModel::class)->tous(),
            'types'      => EvenementModel::TYPES,
        ]);
    }

    public function nouveau(): string
    {
        return $this->formulaire(null);
    }

    public function modifier(int $id): string
    {
        return $this->formulaire($this->trouver($id));
    }

    public function creer(): RedirectResponse
    {
        $donnees = $this->donneesValides(null);
        if (is_string($donnees)) {
            return redirect()->back()->withInput()->with('erreur', $donnees);
        }

        $id = (int) model(EvenementModel::class)->insert($donnees);
        Journaliseur::noter('ajout', 'Evenement', $id, 'Ajout de l\'événement « ' . $donnees['titre'] . ' »');

        return redirect()->to(site_url('admin/evenements'))->with('succes', 'Événement ajouté au planning.');
    }

    public function mettreAJour(int $id): RedirectResponse
    {
        $this->trouver($id);
        $donnees = $this->donneesValides($id);
        if (is_string($donnees)) {
            return redirect()->back()->withInput()->with('erreur', $donnees);
        }

        model(EvenementModel::class)->update($id, $donnees);
        Journaliseur::noter('modification', 'Evenement', $id, 'Modification de l\'événement « ' . $donnees['titre'] . ' »');

        return redirect()->to(site_url('admin/evenements'))->with('succes', 'Événement mis à jour.');
    }

    public function supprimer(int $id): RedirectResponse
    {
        $evenement = $this->trouver($id);

        model(EvenementModel::class)->delete($id);
        Journaliseur::noter('suppression', 'Evenement', $id, 'Suppression de l\'événement « ' . $evenement['titre'] . ' »');

        return redirect()->to(site_url('admin/evenements'))->with('succes', 'Événement supprimé.');
    }

    private function trouver(int $id): array
    {
        $evenement = model(EvenementModel::class)->find($id);

        if ($evenement === null) {
            throw PageNotFoundException::forPageNotFound('Événement introuvable.');
        }

        return $evenement;
    }

    private function formulaire(?array $evenement): string
    {
        return view('admin/evenements/formulaire', [
            'titre'     => $evenement === null ? 'Nouvel événement' : 'Modifier l\'événement',
            'evenement' => $evenement,
            'types'     => EvenementModel::TYPES,
            'showrooms' => model(ShowroomModel::class)->orderBy('ville')->findAll(),
            'robots'    => model(ProduitModel::class)->pourCalculateur(),
        ]);
    }

    /**
     * Valide le formulaire et les règles métier.
     *
     * @return array|string données prêtes pour la base, ou message d'erreur
     */
    private function donneesValides(?int $id): array|string
    {
        $formatDate = 'regex_match[/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/]';
        $regles = [
            'titre'       => ['label' => 'Titre', 'rules' => ['required', 'max_length[150]']],
            'description' => ['label' => 'Description', 'rules' => ['permit_empty', 'max_length[1000]']],
            'type'        => ['label' => 'Type', 'rules' => ['required', 'in_list[' . implode(',', array_keys(EvenementModel::TYPES)) . ']']],
            'dateDebut'   => ['label' => 'Début', 'rules' => ['required', $formatDate]],
            'dateFin'     => ['label' => 'Fin', 'rules' => ['required', $formatDate]],
            'idShowroom'  => ['label' => 'Lieu', 'rules' => ['permit_empty', 'is_natural_no_zero', 'is_not_unique[Showroom.idShowroom]']],
            'idProduit'   => ['label' => 'Robot', 'rules' => ['permit_empty', 'is_natural_no_zero', 'is_not_unique[Produit.idProduit]']],
        ];

        if (! $this->validate($regles)) {
            return implode(' ', $this->validator->getErrors());
        }

        $d     = $this->validator->getValidated();
        $debut = date_sql($d['dateDebut']);
        $fin   = date_sql($d['dateFin']);

        if ($fin <= $debut) { // les dates ISO se comparent comme des chaînes
            return 'La fin doit être postérieure au début.';
        }

        $idShowroom = ($d['idShowroom'] ?? '') !== '' ? (int) $d['idShowroom'] : null;

        if (model(EvenementModel::class)->chevauche($idShowroom, $debut, $fin, $id)) {
            return 'Ce showroom a déjà un événement sur ce créneau.';
        }

        $description = trim((string) ($d['description'] ?? ''));

        return [
            'titre'       => trim($d['titre']),
            'description' => $description === '' ? null : $description,
            'type'        => $d['type'],
            'dateDebut'   => $debut,
            'dateFin'     => $fin,
            'idShowroom'  => $idShowroom,
            'idProduit'   => ($d['idProduit'] ?? '') !== '' ? (int) $d['idProduit'] : null,
        ];
    }
}
```

- [ ] **Step 6 : Vues**

`app/Views/admin/evenements/index.php` :

```php
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1>Gestion du planning</h1>
            <p>Ajoutez, modifiez ou supprimez les événements affichés dans le calendrier.</p>
        </div>
        <a class="btn btn-robotix" href="<?= site_url('admin/evenements/nouveau') ?>">+ Nouvel événement</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="table-responsive formulaire p-0">
            <table class="table table-hover align-middle mb-0">
                <caption class="px-3"><?= count($evenements) ?> événement(s), du plus récent au plus ancien</caption>
                <thead>
                    <tr><th scope="col">Date</th><th scope="col">Titre</th><th scope="col">Type</th><th scope="col">Lieu</th><th scope="col">Robot</th><th scope="col" class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($evenements as $evenement): ?>
                        <tr>
                            <td><?= date_fr($evenement['dateDebut']) ?><br><small class="text-secondary">→ <?= date_fr($evenement['dateFin']) ?></small></td>
                            <th scope="row"><?= esc($evenement['titre']) ?></th>
                            <td><span class="pastille pastille--<?= esc($evenement['type']) ?>"></span><?= esc($types[$evenement['type']] ?? $evenement['type']) ?></td>
                            <td><?= esc($evenement['showroom'] ?? 'Hors showroom') ?></td>
                            <td><?= esc($evenement['produit'] ?? '—') ?></td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/evenements/' . $evenement['idEvenement'] . '/modifier') ?>">Modifier</a>
                                <form class="d-inline" action="<?= site_url('admin/evenements/' . $evenement['idEvenement'] . '/supprimer') ?>" method="post"
                                      data-confirm="Supprimer « <?= esc($evenement['titre']) ?> » ?">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
```

`app/Views/admin/evenements/formulaire.php` :

```php
<?php
$e = $evenement ?? [];
$valeur = static fn (string $champ, string $defaut = ''): string => (string) old($champ, $e[$champ] ?? $defaut, false);
$action = $evenement === null ? site_url('admin/evenements') : site_url('admin/evenements/' . $evenement['idEvenement']);
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="section">
    <div class="container">
        <form class="formulaire formulaire--etroit" action="<?= $action ?>" method="post">
            <?= csrf_field() ?>
            <h1 class="h3 mb-4"><?= esc($titre) ?></h1>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label" for="titre">Titre *</label>
                    <input class="form-control" type="text" id="titre" name="titre" required maxlength="150" value="<?= esc($valeur('titre')) ?>">
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="type">Type *</label>
                    <select class="form-select" id="type" name="type" required>
                        <?php foreach ($types as $code => $libelle): ?>
                            <option value="<?= $code ?>"<?= $valeur('type') === $code ? ' selected' : '' ?>><?= esc($libelle) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="idShowroom">Lieu</label>
                    <select class="form-select" id="idShowroom" name="idShowroom">
                        <option value="">Hors showroom</option>
                        <?php foreach ($showrooms as $showroom): ?>
                            <option value="<?= $showroom['idShowroom'] ?>"<?= $valeur('idShowroom') === (string) $showroom['idShowroom'] ? ' selected' : '' ?>><?= esc($showroom['nom']) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="dateDebut">Début *</label>
                    <input class="form-control" type="datetime-local" id="dateDebut" name="dateDebut" required
                           value="<?= esc(old('dateDebut', date_saisie($e['dateDebut'] ?? null), false)) ?>">
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="dateFin">Fin *</label>
                    <input class="form-control" type="datetime-local" id="dateFin" name="dateFin" required
                           value="<?= esc(old('dateFin', date_saisie($e['dateFin'] ?? null), false)) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="idProduit">Robot présenté</label>
                    <select class="form-select" id="idProduit" name="idProduit">
                        <option value="">Aucun</option>
                        <?php foreach ($robots as $robot): ?>
                            <option value="<?= $robot['idProduit'] ?>"<?= $valeur('idProduit') === (string) $robot['idProduit'] ? ' selected' : '' ?>><?= esc($robot['nom']) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" maxlength="1000"><?= esc($valeur('description')) ?></textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-robotix" type="submit">Enregistrer</button>
                    <a class="btn btn-outline-secondary" href="<?= site_url('admin/evenements') ?>">Annuler</a>
                </div>
            </div>
        </form>
    </div>
</section>

<?= $this->endSection() ?>
```

- [ ] **Step 7 : Lancer le test pour vérifier qu'il passe**

Run : `php vendor/bin/phpunit tests/feature/AdminEvenementsTest.php`
Expected : PASS (12 tests). Si `testCreationValideEtJournalisee` échoue sur la date : vérifier que `date_sql()` produit bien le « T » (format ISO).

- [ ] **Step 8 : Vérification dans le navigateur**

Connecté en admin : créer un événement le 26/11 à Paris, le voir apparaître dans `/evenements` (novembre), le modifier, puis le supprimer (confirmation demandée).

- [ ] **Step 9 : Point de contrôle** — `php vendor/bin/phpunit` : tout est vert.

---

### Task 14 : Validation finale (responsive, W3C) et intégration au portfolio

**Files :**
- Create : `tools/w3c.ps1`
- Create : `docs/recette-ap1.md` (preuves pour l'oral : grille AP1 cochée, captures, résultats W3C)
- Modify : `C:\laragon\www\AP0 AP1\Le Portfolio - 1\projets.html` (nouvelle carte après la carte `#ap1`, vers la ligne 135)

**Interfaces :**
- Consumes : toutes les pages publiques des tâches 4 à 12.

- [ ] **Step 1 : Script de validation W3C**

`tools/w3c.ps1` :

```powershell
# Envoie chaque page publique de Robotix19 au validateur HTML du W3C (validator.w3.org/nu)
# Usage : powershell -ExecutionPolicy Bypass -File tools\w3c.ps1
$base = 'http://localhost/Robotix19/'
$idRobot = (Invoke-RestMethod "$($base)api/evenements?mois=2026-10")[0].idProduit
$pages = @('', 'store', "store/robot/$idRobot", 'news', 'tarifs', 'evenements', 'showrooms', 'contact', 'compte/connexion', 'compte/inscription')
$total = 0

foreach ($page in $pages) {
    $html = (Invoke-WebRequest "$base$page" -UseBasicParsing).Content
    $reponse = Invoke-RestMethod 'https://validator.w3.org/nu/?out=json' -Method Post `
        -ContentType 'text/html; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($html))
    $erreurs = @($reponse.messages | Where-Object { $_.type -eq 'error' })
    $total += $erreurs.Count
    Write-Host ("{0,-22} {1} erreur(s)" -f "/$page", $erreurs.Count)
    $erreurs | ForEach-Object { Write-Host "   ligne $($_.lastLine) : $($_.message)" }
    Start-Sleep -Seconds 1  # le validateur public limite le nombre de requêtes
}

Write-Host "TOTAL : $total erreur(s)"
if ($total -gt 0) { exit 1 }
```

- [ ] **Step 2 : Lancer la validation W3C**

Run : `powershell -ExecutionPolicy Bypass -File tools\w3c.ps1`
Expected : `TOTAL : 0 erreur(s)`. Corriger chaque erreur signalée dans la vue concernée, puis relancer. Les avertissements (`info`) sont tolérés.

- [ ] **Step 3 : Valider le CSS**

Ouvrir https://jigsaw.w3.org/css-validator/#validate_by_input, coller le contenu de `public/css/robotix.css`, choisir le profil « CSS niveau 3 + SVG », puis valider.
Expected : « Félicitations ! Aucune erreur trouvée. » (les variables CSS peuvent produire des avertissements, sans conséquence).

- [ ] **Step 4 : Recette responsive**

Pour chaque page de la liste du script W3C, dans Chrome DevTools (Ctrl+Maj+M), aux largeurs 375, 768 et 1280 px, vérifier :
- aucun défilement horizontal de la page ;
- le menu burger s'ouvre et se referme sous 992 px ;
- l'image réactive garde ses zones alignées (accueil, fiche robot) ;
- le calendrier, la carte et le calculateur restent utilisables.
Faire une capture par largeur pour l'accueil, les tarifs et les événements, dans `docs/captures/`.

- [ ] **Step 5 : Carte Robotix dans le portfolio**

Dans `C:\laragon\www\AP0 AP1\Le Portfolio - 1\projets.html`, insérer juste après la fermeture de la carte `<div class="projet-card" data-category="ap" id="ap1">` (ligne `</div>` qui précède `<!-- AP2 - Site dynamique -->`) :

```html
                    <!-- AP1 - Robotix (CodeIgniter 4) -->
                    <div class="projet-card" data-category="ap" id="ap1-robotix">
                        <div class="projet-image">
                            <div class="projet-badge">AP1</div>
                            <svg class="projet-illus" viewBox="0 0 400 200" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Robot humanoïde">
                                <defs><linearGradient id="pgRobotix" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#0b1020"/><stop offset="1" stop-color="#7c3aed"/></linearGradient></defs>
                                <rect width="400" height="200" fill="url(#pgRobotix)"/>
                                <g class="a-float">
                                    <rect x="178" y="34" width="44" height="40" rx="16" fill="#e2e8f0"/>
                                    <rect x="185" y="47" width="30" height="12" rx="6" fill="#0b1020"/>
                                    <circle cx="193" cy="53" r="3" fill="#00d4ff"/><circle cx="207" cy="53" r="3" fill="#00d4ff"/>
                                    <rect x="170" y="80" width="60" height="62" rx="14" fill="#e2e8f0"/>
                                    <circle cx="200" cy="104" r="8" fill="#00d4ff"/>
                                    <rect x="152" y="84" width="14" height="52" rx="7" fill="#cbd5e1"/>
                                    <rect x="234" y="84" width="14" height="52" rx="7" fill="#cbd5e1"/>
                                    <rect x="178" y="146" width="18" height="40" rx="8" fill="#cbd5e1"/>
                                    <rect x="204" y="146" width="18" height="40" rx="8" fill="#cbd5e1"/>
                                </g>
                            </svg>
                        </div>
                        <div class="projet-content">
                            <h3>Robotix — robots humanoïdes (CodeIgniter 4)</h3>
                            <p>Site responsive de vente de robots humanoïdes : image réactive à zones, catalogue, calculateur de prix en JavaScript, calendrier des événements avec gestion du planning, Google Maps des showrooms, formulaires contrôlés (pattern HTML5 et regex JS), base SQL Server.</p>
                            <div class="projet-tags">
                                <span>HTML5</span>
                                <span>CSS3</span>
                                <span>JavaScript</span>
                                <span>CodeIgniter 4</span>
                                <span>SQL Server</span>
                            </div>
                            <a class="btn primary" href="http://localhost/Robotix19/" target="_blank" rel="noopener">Voir le site</a>
                        </div>
                    </div>
```

- [ ] **Step 6 : Vérifier le portfolio**

Ouvrir `http://localhost/AP0%20AP1/Le%20Portfolio%20-%201/projets.html` : la carte Robotix apparaît sous l'onglet « AP », le bouton ouvre Robotix dans un nouvel onglet. Passer `projets.html` au validateur W3C (« Validate by File Upload »).
Expected : aucune nouvelle erreur due à la carte ajoutée.

- [ ] **Step 7 : Fiche de recette**

`docs/recette-ap1.md` : reprendre la grille AP1, avec pour chaque critère la page, la manipulation à montrer à l'oral et le résultat obtenu (✅). Contenu :

```markdown
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
```

- [ ] **Step 8 : Point de contrôle final**

Run : `php vendor/bin/phpunit` puis `node --test tests/js/` puis `powershell -ExecutionPolicy Bypass -File tools\w3c.ps1`
Expected : tout est vert, 0 erreur W3C.

---

## Écarts assumés par rapport à la spec

- **Pas de `layouts/admin.php`** : les pages d'administration utilisent `layouts/main.php`, car CodeIgniter ne permet pas à un layout d'en étendre un autre. Une seule administration (le planning) ne justifie pas un second gabarit.
- **Pas de `JournalModel`** : l'écriture dans `Journal` passe par `App\Libraries\Journaliseur` (une seule méthode, un seul usage).
- **Pas de `srcset`** : les visuels sont des SVG vectoriels, nets à toutes les résolutions ; le responsive est assuré par `img-fluid`, `aspect-ratio` et les attributs `width`/`height`.
- **Section « derniers articles » de l'accueil** : remplacée par la carte « Robotix News » de la section « Notre activité » ; les articles arrivent avec le sous-projet 2.
- **Consultation des messages de contact dans l'admin** : reportée au sous-projet 1 bis (back-office complet). Les messages sont déjà enregistrés en base.
