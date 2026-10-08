<?php

namespace App\Database\Seeds;

use App\Libraries\CalendrierDemo;
use App\Libraries\IllustrationRobot;
use CodeIgniter\Database\Seeder;

/**
 * Données de démonstration Robotix. Rejouable : vide les tables puis les remplit.
 * Usage : php spark db:seed RobotixSeeder
 */
class RobotixSeeder extends Seeder
{
    private const MOT_DE_PASSE = 'Robotix2026!';

    /** Date de référence pour classer les adhérents de démonstration (catégories stables d'une année sur l'autre). */
    private const DATE_REFERENCE = '2026-09-01';

    /** Décalage des dates de démonstration (voir CalendrierDemo). */
    private CalendrierDemo $cal;

    public function run(): void
    {
        helper('robotix');
        // Dates toujours actuelles : décalage en semaines depuis la référence (forçable par ROBOTIX_DEMO_SEMAINES)
        $forcees = getenv('ROBOTIX_DEMO_SEMAINES');
        $semaines = $forcees !== false && $forcees !== '' ? (int) $forcees : CalendrierDemo::semainesDepuisReference(date('Y-m-d'));
        $this->cal = new CalendrierDemo($semaines);
        CalendrierDemo::memoriser($semaines);
        $this->viderTables();
        $marques    = $this->creerMarques();
        $categories = $this->creerCategories();
        $produits   = $this->creerProduits($marques, $categories);
        $this->creerCompatibilites($produits);
        $showrooms  = $this->creerShowrooms();
        $evenements = $this->creerEvenements($showrooms, $produits);
        $this->creerComptes();
        $tarifs        = $this->creerTarifs();
        $categoriesAge = $this->creerCategoriesAge();
        $adherents = $this->creerClub($categories, $categoriesAge, $tarifs, $evenements);
        $this->creerClubAp3($adherents, $categoriesAge, $evenements, $showrooms, $produits);
    }

    private function viderTables(): void
    {
        // Ordre : des tables qui référencent vers les tables référencées
        $tables = [
            'MailAEnvoyer', 'HistoriqueAdhesion', 'PointOrdreJour', 'Convocation', 'Reunion', 'Inscription',
            'Panier', 'ClientInteret', 'Adhesion', 'TentativeConnexion', 'JetonMotDePasse',
            'Journal', 'HistoriqueStatut', 'Paiement', 'Contenir', 'Commande', 'Adresse',
            'Mentionner', 'Aborder', 'Article', 'Suivre', 'Evenement', 'MessageContact', 'Showroom',
            'CompatibiliteProduit', 'Image', 'Produit', 'Categorie', 'Marque', 'Thematique',
            'Membre', 'Animateur', 'Client', 'Redacteur', 'Administrateur', 'Utilisateur',
            'Reduction', 'CategorieAge', 'Tarif',
        ];

        foreach ($tables as $table) {
            $this->db->query("DELETE FROM [$table]");
        }
    }

    /** @return array<string, int> nom => idMarque */
    private function creerMarques(): array
    {
        // Données réelles vérifiées : voir docs/sources-robots.md
        // [nom, initiales du logo, couleur, pays, siège, année de création (null si non vérifiée), site officiel, description]
        $marques = [
            ['Unitree Robotics', 'UR', '#00d4ff', 'Chine', 'Hangzhou, Chine', 2016, 'https://www.unitree.com',
             'Fondée en août 2016 à Hangzhou par Wang Xingxing, Unitree s\'est d\'abord fait connaître avec ses robots quadrupèdes, puis a lancé ses humanoïdes H1 et G1 en 2024.'],
            ['Figure AI', 'FA', '#a855f7', 'États-Unis', 'San Jose, Californie, États-Unis', 2022, 'https://www.figure.ai',
             'Fondée en 2022 par Brett Adcock, Figure AI développe des humanoïdes généralistes pilotés par l\'intelligence artificielle. Partenariat avec BMW annoncé en janvier 2024.'],
            ['Pollen Robotics', 'PR', '#ef4444', 'France', 'Bordeaux, France', 2016, 'https://www.pollen-robotics.com',
             'Fondée en 2016 à Bordeaux par Matthieu Lapeyre et Pierre Rouanet, créatrice des robots open source Reachy (2e place de l\'Avatar XPRIZE en 2022). Rachetée par Hugging Face en avril 2025.'],
            ['Aldebaran (Maxtronics)', 'AL', '#38bdf8', 'France', 'Paris, France', 2005, 'https://maxtronics.com',
             'Société française à l\'origine des robots NAO et Pepper. Placée en redressement judiciaire en juin 2025, ses actifs ont été rachetés en juillet 2025 par le groupe chinois Maxvision, qui poursuit l\'activité sous le nom Maxtronics.'],
            ['XPeng', 'XP', '#22d3ee', 'Chine', 'Guangzhou, Chine', 2014, 'https://www.xpeng.com',
             'Constructeur chinois de véhicules électriques coté à New York et à Hong Kong. Il a présenté son robot humanoïde Iron le 8 novembre 2024 lors de son AI Day.'],
            ['Engineered Arts', 'EA', '#f59e0b', 'Royaume-Uni', 'Falmouth, Cornouailles, Royaume-Uni', 2004, 'https://engineeredarts.com',
             'Fondée en octobre 2004 par Will Jackson, l\'entreprise conçoit et fabrique des robots humanoïdes sociaux, dont Ameca, présenté au public au CES 2022 de Las Vegas.'],
            ['AgiBot', 'AG', '#10b981', 'Chine', 'Shanghai, Chine', 2023, 'https://www.agibot.com',
             'Fondée en février 2023 à Shanghai par d\'anciens ingénieurs de Huawei, Deng Taihua et Peng Zhihui. Gammes Yuanzheng, Lingxi et Genie.'],
            ['PAL Robotics', 'PA', '#f97316', 'Espagne', 'Barcelone, Espagne', 2004, 'https://pal-robotics.com',
             'Fondée en 2004 à Barcelone, PAL Robotics conçoit et fabrique en Catalogne ses robots REEM-C, TALOS, TIAGo et ARI, vendus dans plus de 30 pays.'],
            ['Poppy Project (Inria)', 'PP', '#e879f9', 'France', 'Bordeaux, France', null, 'https://www.poppy-project.org',
             'Projet open source né dans l\'équipe Flowers d\'Inria à Bordeaux : robots humanoïdes imprimés en 3D (matériel sous licence CC BY-SA, logiciel Pypot en Python) utilisés dans la recherche et l\'enseignement.'],
            ['Istituto Italiano di Tecnologia', 'IIT', '#84cc16', 'Italie', 'Gênes, Italie', 2003, 'https://www.iit.it',
             'Centre de recherche scientifique italien basé à Gênes. Il développe iCub, issu du consortium européen RobotCub (2004-2010).'],
        ];

        $dossier = FCPATH . 'images/marques/';
        is_dir($dossier) || mkdir($dossier, 0775, true);
        $ids = [];

        foreach ($marques as [$nom, $initiales, $couleur, $pays, $siege, $annee, $site, $description]) {
            $fichier = url_title($nom, '-', true) . '.svg';
            file_put_contents($dossier . $fichier, IllustrationRobot::logo($initiales, $couleur));

            $this->db->table('Marque')->insert([
                'nom' => $nom, 'pays' => $pays, 'siege' => $siege, 'anneeCreation' => $annee,
                'siteWeb' => $site, 'logo' => $fichier, 'description' => $description,
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
        // Robots réels (caractéristiques et statuts vérifiés : docs/sources-robots.md).
        // Les prix sont fictifs (boutique de démonstration) ; le statut commercial est réel.
        // [référence, nom, catégorie, marque, prix HT fictif, stock, statut commercial réel, description]
        $produits = [
            ['RBX-UNI-G1', 'Unitree G1', 'Compagnon', 'Unitree Robotics', 14900.00, 8, 'Commercialisé',
             'Humanoïde de taille moyenne (environ 1,27 m pour 35 kg) lancé en août 2024 au prix public d\'environ 16 000 dollars. Il compte de 23 à 43 degrés de liberté selon la version et offre environ 2 heures d\'autonomie.'],
            ['RBX-FIG-02', 'Figure 02', 'Domestique', 'Figure AI', 89900.00, 2, 'Non commercialisé aux particuliers',
             'Présenté en août 2024, Figure 02 dispose de mains à cinq doigts (16 degrés de liberté), de six caméras et d\'un modèle d\'IA vision-langage-action embarqué. Il peut porter jusqu\'à 25 kg et a été testé dans une usine BMW. Son successeur, Figure 03, a été présenté en octobre 2025.'],
            ['RBX-POL-R2', 'Reachy 2', 'Éducatif', 'Pollen Robotics', 64900.00, 3, 'Commercialisé',
             'Humanoïde open source annoncé fin 2024 pour la recherche et l\'enseignement : deux bras à 7 degrés de liberté, tête expressive, base mobile, programmable en Python. Vendu environ 70 000 dollars.'],
            ['RBX-ALD-PEPPER', 'Pepper', 'Compagnon', 'Aldebaran (Maxtronics)', 15900.00, 0, 'Production arrêtée',
             'Robot semi-humanoïde de 1,20 m pour 28 kg, lancé au Japon en juin 2014 et conçu pour reconnaître les émotions (quatre micros, deux caméras HD, capteur de profondeur). Production suspendue en juin 2021 après environ 27 000 exemplaires.'],
            ['RBX-XPG-IRON', 'XPeng Iron', 'Premium', 'XPeng', 119900.00, 0, 'Commercialisation prévue',
             'Humanoïde présenté le 8 novembre 2024 : environ 1,73 m pour 70 kg et plus de 60 articulations. Déjà utilisé sur les chaînes de production de XPeng ; commercialisation plus large visée à partir de 2027.'],
            ['RBX-EA-AMECA', 'Ameca', 'Premium', 'Engineered Arts', 149900.00, 1, 'Vendu ou loué aux professionnels',
             'Humanoïde social créé en 2021 et présenté au CES 2022 : visage en caoutchouc gris très expressif, caméras dans les yeux, micros intégrés, conversation pilotée par de grands modèles de langage ou par téléprésence.'],
            ['RBX-AGB-X2', 'AgiBot X2', 'Compagnon', 'AgiBot', 24900.00, 4, 'Commercialisé',
             'Humanoïde bipède compact (environ 1,31 m pour 35 kg), aussi appelé Lingxi X2, dévoilé le 11 mars 2025. Destiné à l\'éducation, à la recherche, à l\'accueil et au divertissement.'],
            ['RBX-PAL-ARI', 'ARI', 'Domestique', 'PAL Robotics', 59900.00, 2, 'Vendu ou loué aux professionnels',
             'Robot humanoïde social conçu pour l\'interaction homme-robot : il parle, reconnaît les visages, fait des gestes et dispose d\'un écran tactile sur le torse. Utilisé pour l\'accueil, la santé et la recherche.'],
            ['RBX-POP-HUM', 'Poppy Humanoid', 'Éducatif', 'Poppy Project (Inria)', 9900.00, 10, 'Open source, à assembler',
             'Humanoïde open source de 85 cm imprimé en 3D et animé par des moteurs Dynamixel, conçu par l\'équipe Flowers d\'Inria. Programmable en Snap! ou en Python, il s\'assemble en environ sept heures.'],
            ['RBX-IIT-ICUB', 'iCub', 'Premium', 'Istituto Italiano di Tecnologia', 229900.00, 1, 'Plateforme de recherche',
             'Humanoïde de recherche de 1,04 m pour 22 kg, à la taille d\'un enfant de trois ans et demi, avec 53 degrés de liberté. Logiciel libre (GPL/LGPL) ; une trentaine d\'exemplaires équipent des laboratoires, pour environ 250 000 euros l\'unité.'],
        ];

        // Vraies photos (Wikimedia Commons, licences libres) : [fichier, légende, auteur à créditer, licence, page source]
        $photos = [
            'RBX-UNI-G1' => [
                ['rbx-uni-g1-1.jpg', 'Unitree G1 au salon UAV Expo 2024', 'Sayanesy', 'CC0', 'https://commons.wikimedia.org/wiki/File:Unitree_G1.jpg'],
                ['rbx-uni-g1-2.jpg', 'Unitree G1 sur scène au Japan Mobility Show 2025 (Tokyo)', 'RuinDig/Yuki Uchida', 'CC BY 4.0', 'https://commons.wikimedia.org/wiki/File:Japan-Mobility-Show-2025-RuinDig_0560.jpg'],
            ],
            'RBX-FIG-02' => [
                ['rbx-fig-02-1.jpg', 'Figure 02 en train de trier des colis (image tirée d\'une vidéo)', 'newscreators (YouTube)', 'CC BY 3.0', 'https://commons.wikimedia.org/wiki/File:Robot_package_handling.webm'],
            ],
            'RBX-POL-R2' => [
                ['rbx-pol-r2-1.jpg', 'Reachy (première génération) à l\'École polytechnique', 'École polytechnique', 'CC BY-SA 2.0', 'https://commons.wikimedia.org/wiki/File:Reachy_robot_-_%C3%89cole_Polytechnique.jpg'],
            ],
            'RBX-ALD-PEPPER' => [
                ['rbx-ald-pepper-1.jpg', 'Pepper à Tokyo', 'Øyvind Holmstad', 'CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:A_ROBOT_IN_TOKYO.jpg'],
                ['rbx-ald-pepper-2.jpg', 'Pepper, vue rapprochée', 'Alex Knight', 'CC0', 'https://commons.wikimedia.org/wiki/File:Alex_Knight_2017-01-30_(Unsplash).jpg'],
            ],
            'RBX-XPG-IRON' => [
                ['rbx-xpg-iron-1.jpg', 'XPeng Iron au salon de l\'automobile de Guangzhou 2025', 'Tim Wu', 'CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:XPeng_Iron_at_Auto_Guangzhou_2025_20251123.jpg'],
                ['rbx-xpg-iron-2.jpg', 'XPeng Iron au salon IAA Summit 2025 de Munich', 'Matti Blume', 'CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:XPeng,_IAA_Summit_2025,_Munich_(20250908-P1049571).jpg'],
            ],
            'RBX-EA-AMECA' => [
                ['rbx-ea-ameca-1.jpg', 'Ameca, première génération', 'Willy Jackson', 'CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Ameca_Generation_1.jpg'],
            ],
            'RBX-AGB-X2' => [
                ['rbx-agb-x2-1.jpg', 'AgiBot X2 au Mobile World Congress 2026 (Barcelone)', 'JJxFile', 'CC BY 4.0', 'https://commons.wikimedia.org/wiki/File:AGIBOT-X2-_by_JxFile_MWC26.jpg'],
            ],
            'RBX-PAL-ARI' => [
                ['rbx-pal-ari-1.jpg', 'ARI au musée des sciences et techniques de Catalogne (Terrassa)', 'Enric', 'CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:202_MNACTEC_(Terrassa),_ARI,_robot_d%27assist%C3%A8ncia_de_Pal_Robotics,_amb_l%27%C3%80gora_al_fons.jpg'],
            ],
            'RBX-POP-HUM' => [
                ['rbx-pop-hum-1.jpg', 'Poppy Humanoid, robot open source imprimé en 3D', 'Inria / Poppy-project.org / Photo H. Raguet', 'CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Open-Source_3D_printed_Poppy_humanoid_robot.jpg'],
                ['rbx-pop-hum-2.jpg', 'Poppy Humanoid en position assise', 'Inria / Poppy-project.org / Photo H. Raguet', 'CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:Open-Source_3D_printed_Poppy_humanoid_robot_(sit).jpg'],
            ],
            'RBX-IIT-ICUB' => [
                ['rbx-iit-icub-1.jpg', 'iCub au Festival de l\'économie 2018 (Trente)', 'Niccolò Caranti', 'CC BY-SA 4.0', 'https://commons.wikimedia.org/wiki/File:ICub_-_Festival_Economia_2018_1.jpg'],
                ['rbx-iit-icub-2.jpg', 'iCub au salon Innorobo 2014 (Lyon)', 'Xavier Caré / Wikimedia Commons / CC-BY-SA', 'CC BY-SA 3.0', 'https://commons.wikimedia.org/wiki/File:ICub_Innorobo_Lyon_2014_debout.JPG'],
            ],
        ];

        $dossier = FCPATH . 'images/robots/';
        is_dir($dossier) || mkdir($dossier, 0775, true);
        file_put_contents($dossier . 'defaut.svg', IllustrationRobot::robot('#64748b', 1));
        $ids = [];

        foreach ($produits as [$reference, $nom, $categorie, $marque, $prix, $stock, $statut, $description]) {
            $this->db->table('Produit')->insert([
                'reference' => $reference, 'nom' => $nom, 'description' => $description,
                'prixHt' => $prix, 'stock' => $stock, 'tauxTva' => 20.00, 'statutCommercial' => $statut,
                'idCategorie' => $categories[$categorie], 'idMarque' => $marques[$marque],
            ]);
            $id = (int) $this->db->insertID();
            $ids[$reference] = $id;

            foreach ($photos[$reference] as $index => [$fichier, $legende, $credit, $licence, $source]) {
                $this->db->table('Image')->insert([
                    'fichier' => $fichier, 'legende' => $legende, 'numOrdre' => $index + 1, 'idProduit' => $id,
                    'credit' => $credit, 'licence' => $licence, 'source' => $source,
                ]);
            }
        }

        return $ids;
    }

    private function creerCompatibilites(array $produits): void
    {
        $paires = [
            ['RBX-UNI-G1', 'RBX-AGB-X2'], ['RBX-FIG-02', 'RBX-XPG-IRON'],
            ['RBX-ALD-PEPPER', 'RBX-PAL-ARI'], ['RBX-POL-R2', 'RBX-POP-HUM'],
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

    /** @return array<string, int> titre => idEvenement */
    private function creerEvenements(array $showrooms, array $produits): array
    {
        // [titre, type, début, fin, ville du showroom ou null, référence produit ou null, description]
        $evenements = [
            ['Démonstration Unitree G1', 'demo', '2026-10-08T14:00:00', '2026-10-08T17:00:00', 'Paris', 'RBX-UNI-G1', 'Venez voir le G1 marcher, saluer et jouer au ballon.'],
            ['Présentation Ameca en France', 'lancement', '2026-10-15T10:00:00', '2026-10-15T18:00:00', 'Lyon', 'RBX-EA-AMECA', 'Rencontre avec Ameca et démonstration de ses expressions faciales.'],
            ['Atelier programmation Poppy', 'atelier', '2026-10-21T18:30:00', '2026-10-21T20:30:00', 'Marseille', 'RBX-POP-HUM', 'Initiation à la programmation de Poppy en Python, dès 10 ans (12 places).'],
            ['Salon des robots humanoïdes — Lyon Eurexpo', 'salon', '2026-10-24T09:00:00', '2026-10-25T19:00:00', null, null, 'Retrouvez Robotix sur le stand B12 pendant deux jours.'],
            ['Démonstration Pepper', 'demo', '2026-11-05T14:00:00', '2026-11-05T17:00:00', 'Lyon', 'RBX-ALD-PEPPER', 'Pepper en situation d\'accueil dans un salon reconstitué.'],
            ['Atelier : apprendre un geste à Reachy 2', 'atelier', '2026-11-12T18:30:00', '2026-11-12T20:30:00', 'Paris', 'RBX-POL-R2', 'Apprentissage par démonstration avec les bras de Reachy 2.'],
            ['Journée XPeng Iron', 'lancement', '2026-11-19T10:00:00', '2026-11-19T18:00:00', 'Paris', 'RBX-XPG-IRON', 'Présentation de XPeng Iron, robot pas encore commercialisé, et échanges avec nos conseillers.'],
            ['Démonstration ARI', 'demo', '2026-11-25T14:00:00', '2026-11-25T17:00:00', 'Marseille', 'RBX-PAL-ARI', 'ARI accueille les visiteurs et répond à leurs questions sur son écran tactile.'],
            ['Démonstration AgiBot X2', 'demo', '2026-12-03T14:00:00', '2026-12-03T17:00:00', 'Paris', 'RBX-AGB-X2', 'Découvrez l\'AgiBot X2, humanoïde compact dévoilé en mars 2025.'],
            ['Atelier famille Poppy', 'atelier', '2026-12-09T18:30:00', '2026-12-09T20:30:00', 'Lyon', 'RBX-POP-HUM', 'Programmer une danse de Noël avec Poppy, en famille.'],
            ['Marché de Noël des robots', 'salon', '2026-12-12T10:00:00', '2026-12-12T18:00:00', 'Marseille', null, 'Toute la gamme exposée, offres spéciales.'],
            ['Démonstration Figure 02', 'demo', '2026-12-17T14:00:00', '2026-12-17T17:00:00', 'Lyon', 'RBX-FIG-02', 'Présentation en vidéo des capacités de Figure 02, testé en usine chez BMW.'],
        ];

        $places = ['demo' => 30, 'lancement' => 50, 'atelier' => 12, 'salon' => 200];
        $ids    = [];

        foreach ($evenements as [$titre, $type, $debut, $fin, $ville, $reference, $description]) {
            $this->db->table('Evenement')->insert([
                'titre' => $titre, 'type' => $type, 'description' => $description,
                'dateDebut' => $this->d($debut), 'dateFin' => $this->d($fin), 'nbPlaces' => $places[$type],
                'idShowroom' => $ville === null ? null : $showrooms[$ville],
                'idProduit' => $reference === null ? null : $produits[$reference],
            ]);
            $ids[$titre] = (int) $this->db->insertID();
        }

        return $ids;
    }

    private function creerComptes(): void
    {
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

    private function creerUtilisateur(string $nom, string $prenom, string $email, ?string $dateInscription = null): int
    {
        $donnees = [
            'nom' => $nom, 'prenom' => $prenom, 'email' => $email,
            'motDePasse' => password_hash(self::MOT_DE_PASSE, PASSWORD_DEFAULT), 'actif' => 1,
        ];
        if ($dateInscription !== null) {
            $donnees['dateInscription'] = $this->d($dateInscription);
        }
        $this->db->table('Utilisateur')->insert($donnees);

        return (int) $this->db->insertID();
    }

    /** @return array<string, array{id: int, valeur: float}> code => tarif */
    private function creerTarifs(): array
    {
        $tarifs = [
            ['decouverte', 'Découverte', 'formule', 'fixe', 49.00, 'Ateliers mensuels et newsletter du club.'],
            ['passion', 'Passion', 'formule', 'fixe', 99.00, 'Ateliers illimités, 10 % sur les accessoires, priorité aux démonstrations.'],
            ['premium', 'Premium', 'formule', 'fixe', 199.00, 'Avantages Passion + un week-end d\'essai d\'un robot à domicile chaque année.'],
            ['garantie', 'Garantie étendue 3 ans', 'option', 'pourcentage', 12.00, '12 % du prix HT du robot : pièces, main-d\'œuvre et robot de prêt.'],
            ['livraison', 'Livraison et installation', 'option', 'fixe', 290.00, 'Livraison à domicile, déballage, cartographie du logement et mise en service.'],
            ['maintenance', 'Contrat de maintenance (1 an)', 'option', 'fixe', 490.00, 'Deux visites d\'entretien et les mises à jour logicielles prioritaires.'],
            ['formation', 'Prise en main (2 h)', 'option', 'fixe', 150.00, 'Un technicien vous apprend à programmer les routines de votre robot.'],
        ];
        $ids = [];

        foreach ($tarifs as [$code, $libelle, $famille, $mode, $valeur, $description]) {
            $this->db->table('Tarif')->insert([
                'code' => $code, 'libelle' => $libelle, 'famille' => $famille,
                'mode' => $mode, 'valeur' => $valeur, 'description' => $description,
            ]);
            $ids[$code] = ['id' => (int) $this->db->insertID(), 'valeur' => $valeur];
        }

        return $ids;
    }

    /** @return array<string, array{id: int, ageMin: int, ageMax: int, taux: float}> libellé => catégorie */
    private function creerCategoriesAge(): array
    {
        $categories = [['Jeune', 18, 24, 15.00], ['Adulte', 25, 59, 0.00], ['Senior', 60, 120, 10.00]];
        $resultat   = [];

        foreach ($categories as [$libelle, $ageMin, $ageMax, $taux]) {
            $this->db->table('CategorieAge')->insert(['libelle' => $libelle, 'ageMin' => $ageMin, 'ageMax' => $ageMax]);
            $id = (int) $this->db->insertID();
            $this->db->table('Reduction')->insert(['idCategorieAge' => $id, 'txReduction' => $taux]);
            $resultat[$libelle] = ['id' => $id, 'ageMin' => $ageMin, 'ageMax' => $ageMax, 'taux' => $taux];
        }

        return $resultat;
    }

    /**
     * 12 adhérents du Club Robotix, leurs centres d'intérêt, adhésions 2025-2026 et 2 réservations.
     */
    /** @return array<string, int> e-mail => idUtilisateur */
    private function creerClub(array $categoriesProduit, array $categoriesAge, array $tarifs, array $evenements): array
    {
        // [prénom, nom, e-mail, naissance, téléphone, intérêts, formule 2025, formule 2026]
        $adherents = [
            ['Camille', 'Martin', 'client@robotix.test', '1994-03-12', '06 12 34 56 78', ['Domestique', 'Compagnon'], 'passion', 'passion'],
            ['Lucas', 'Bernard', 'lucas.bernard@exemple.fr', '2004-06-02', '06 21 43 65 87', ['Éducatif'], null, 'decouverte'],
            ['Emma', 'Petit', 'emma.petit@exemple.fr', '2005-11-20', '06 32 54 76 98', ['Éducatif', 'Compagnon'], 'decouverte', 'passion'],
            ['Nathan', 'Robert', 'nathan.robert@exemple.fr', '1990-01-15', '07 11 22 33 44', ['Premium'], 'premium', 'premium'],
            ['Chloé', 'Richard', 'chloe.richard@exemple.fr', '1987-09-09', '07 22 33 44 55', ['Domestique'], null, 'passion'],
            ['Louis', 'Durand', 'louis.durand@exemple.fr', '1958-04-30', '04 72 10 20 30', ['Compagnon'], 'passion', 'passion'],
            ['Manon', 'Leroy', 'manon.leroy@exemple.fr', '2003-02-14', '06 44 55 66 77', ['Éducatif'], null, 'decouverte'],
            ['Jules', 'Moreau', 'jules.moreau@exemple.fr', '1975-12-01', '06 55 66 77 88', ['Domestique', 'Premium'], 'premium', null],
            ['Inès', 'Simon', 'ines.simon@exemple.fr', '1962-07-07', '04 91 20 30 40', ['Compagnon'], null, 'decouverte'],
            ['Paul', 'Laurent', 'paul.laurent@exemple.fr', '1950-10-10', '01 42 30 40 50', ['Domestique'], 'decouverte', null],
            ['Sarah', 'Lefebvre', 'sarah.lefebvre@exemple.fr', '1999-08-25', '06 66 77 88 99', ['Éducatif'], null, 'premium'],
            ['Hélène', 'Michel', 'helene.michel@exemple.fr', '1963-05-05', '06 77 88 99 00', ['Compagnon', 'Domestique'], 'passion', 'passion'],
        ];
        $ids = [];

        foreach ($adherents as $i => [$prenom, $nom, $email, $naissance, $telephone, $interets, $formule2025, $formule2026]) {
            $mois       = sprintf('%02d', 1 + $i % 8);
            $inscrit    = ($formule2025 !== null ? '2025' : '2026') . "-$mois-15T10:00:00";
            $age        = age_le($this->d($naissance), $this->d(self::DATE_REFERENCE));
            $categorie  = array_values(array_filter($categoriesAge, static fn ($c) => $age >= $c['ageMin'] && $age <= $c['ageMax']))[0];
            $id         = $this->creerUtilisateur($nom, $prenom, $email, $inscrit);
            $ids[$email] = $id;

            $this->db->table('Client')->insert([
                'idUtilisateur' => $id, 'telephone' => $telephone,
                'dateNaissance' => $this->d($naissance), 'idCategorieAge' => $categorie['id'],
            ]);

            foreach ($interets as $interet) {
                $this->db->table('ClientInteret')->insert(['idUtilisateur' => $id, 'idCategorie' => $categoriesProduit[$interet]]);
            }

            foreach ([2025 => $formule2025, 2026 => $formule2026] as $annee => $formule) {
                if ($formule === null) {
                    continue;
                }
                $this->db->table('Adhesion')->insert([
                    'idUtilisateur' => $id, 'annee' => $this->cal->annee($annee), 'dateAdhesion' => $this->d("$annee-$mois-15T10:00:00"),
                    'idTarif' => $tarifs[$formule]['id'],
                    'montant' => round($tarifs[$formule]['valeur'] * (1 - $categorie['taux'] / 100), 2),
                ]);
            }
        }

        $this->db->table('Adresse')->insert([
            'libelle' => 'Domicile', 'ligne1' => '10 rue de la République', 'codePostal' => '69002',
            'ville' => 'Lyon', 'pays' => 'France', 'idUtilisateur' => $ids['client@robotix.test'],
        ]);

        $reservations = [
            ['client@robotix.test', 'Atelier programmation Poppy', 2],
            ['emma.petit@exemple.fr', 'Démonstration Unitree G1', 3],
        ];
        foreach ($reservations as [$email, $titre, $places]) {
            $this->db->table('Panier')->insert([
                'idEvenement' => $evenements[$titre], 'idUtilisateur' => $ids[$email],
                'nomEvenement' => $titre, 'dateResa' => $this->d('2026-10-01T09:00:00'), 'nbPlace' => $places,
            ]);
        }

        return $ids;
    }

    /**
     * AP3 : animateurs (avec remplaçants), membres, pseudos, événements passés de septembre,
     * inscriptions avec présence et travail réalisé, réunions avec convocations et ordre du jour.
     */
    private function creerClubAp3(array $adherents, array $categoriesAge, array $evenements, array $showrooms, array $produits): void
    {
        // Animateurs : des adhérents (clients) spécialisés
        $animateurs = [
            'karim' => ['Haddad', 'Karim', 'karim.haddad@robotix.test', 'karim.h', '1985-02-11', '06 10 20 30 40', 'Programmation des robots'],
            'ines'  => ['Fontaine', 'Inès', 'ines.fontaine@robotix.test', 'ines.f', '1990-06-21', '06 20 30 40 50', 'Robotique domestique'],
            'theo'  => ['Garnier', 'Théo', 'theo.garnier@robotix.test', 'theo.g', '1992-12-03', '06 30 40 50 60', 'Robots éducatifs'],
        ];
        $ids = [];
        foreach ($animateurs as $cle => [$nom, $prenom, $email, $pseudo, $naissance, $telephone, $specialite]) {
            $id = $this->creerUtilisateur($nom, $prenom, $email, '2025-09-01T09:00:00');
            $this->db->table('Utilisateur')->where('idUtilisateur', $id)->update(['pseudo' => $pseudo]);
            $this->db->table('Client')->insert([
                'idUtilisateur' => $id, 'telephone' => $telephone, 'dateNaissance' => $this->d($naissance),
                'idCategorieAge' => $categoriesAge['Adulte']['id'],
            ]);
            $this->db->table('Animateur')->insert(['idUtilisateur' => $id, 'specialite' => $specialite]);
            $ids[$cle] = $id;
        }
        // Réflexivité : chaque animateur a un remplaçant
        foreach (['karim' => 'ines', 'ines' => 'theo', 'theo' => 'karim'] as $titulaire => $remplacant) {
            $this->db->table('Animateur')->where('idUtilisateur', $ids[$titulaire])->update(['idRemplacant' => $ids[$remplacant]]);
        }

        // Événements passés (septembre 2026) pour disposer de présences et de travaux réalisés
        $passes = [
            ['Atelier découverte Poppy', 'atelier', '2026-09-09T18:30:00', '2026-09-09T20:30:00', 'Marseille', 'RBX-POP-HUM', 12, 'karim'],
            ['Démonstration de rentrée Figure 02', 'demo', '2026-09-16T14:00:00', '2026-09-16T17:00:00', 'Paris', 'RBX-FIG-02', 30, 'ines'],
            ['Atelier programmation AgiBot X2', 'atelier', '2026-09-23T18:00:00', '2026-09-23T21:00:00', 'Lyon', 'RBX-AGB-X2', 12, 'theo'],
        ];
        foreach ($passes as [$titre, $type, $debut, $fin, $ville, $reference, $places, $animateur]) {
            $this->db->table('Evenement')->insert([
                'titre' => $titre, 'type' => $type, 'description' => 'Événement de rentrée du Club Robotix.',
                'dateDebut' => $this->d($debut), 'dateFin' => $this->d($fin), 'nbPlaces' => $places,
                'idShowroom' => $showrooms[$ville], 'idProduit' => $produits[$reference], 'idAnimateur' => $ids[$animateur],
            ]);
            $evenements[$titre] = (int) $this->db->insertID();
        }
        // Animateur de chaque événement selon son type
        foreach (['demo' => 'ines', 'atelier' => 'karim', 'lancement' => 'theo', 'salon' => 'theo'] as $type => $animateur) {
            $this->db->table('Evenement')->where('type', $type)->where('idAnimateur', null)->update(['idAnimateur' => $ids[$animateur]]);
        }

        // Membres (joueurs) : 9 des 12 adhérents
        $membres = [
            'client@robotix.test' => 'intermédiaire', 'lucas.bernard@exemple.fr' => 'confirmé', 'emma.petit@exemple.fr' => 'débutant',
            'nathan.robert@exemple.fr' => 'confirmé', 'chloe.richard@exemple.fr' => 'débutant', 'louis.durand@exemple.fr' => 'débutant',
            'manon.leroy@exemple.fr' => 'intermédiaire', 'sarah.lefebvre@exemple.fr' => 'intermédiaire', 'helene.michel@exemple.fr' => 'débutant',
        ];
        foreach ($membres as $email => $niveau) {
            $this->db->table('Membre')->insert(['idUtilisateur' => $adherents[$email], 'niveau' => $niveau]);
        }
        $this->db->table('Utilisateur')->where('email', 'client@robotix.test')->update(['pseudo' => 'camille']);

        // Inscriptions : [e-mail, événement, présent (null = pas encore pointé), travail réalisé]
        $inscriptions = [
            ['client@robotix.test', 'Atelier découverte Poppy', 1, 'A programmé un salut de la tête en Python.'],
            ['lucas.bernard@exemple.fr', 'Atelier découverte Poppy', 1, 'A créé une animation des bras.'],
            ['emma.petit@exemple.fr', 'Atelier découverte Poppy', 0, null],
            ['manon.leroy@exemple.fr', 'Atelier découverte Poppy', 1, 'A fait suivre un visage à Poppy.'],
            ['client@robotix.test', 'Démonstration de rentrée Figure 02', 1, 'Questions sur la mise en service à domicile.'],
            ['louis.durand@exemple.fr', 'Démonstration de rentrée Figure 02', 1, null],
            ['helene.michel@exemple.fr', 'Démonstration de rentrée Figure 02', 0, null],
            ['lucas.bernard@exemple.fr', 'Atelier programmation AgiBot X2', 1, 'Chorégraphie de huit pas.'],
            ['emma.petit@exemple.fr', 'Atelier programmation AgiBot X2', 1, 'Programme d\'équilibre sur une jambe.'],
            ['nathan.robert@exemple.fr', 'Atelier programmation AgiBot X2', 1, 'Script de marche en carré.'],
            ['sarah.lefebvre@exemple.fr', 'Atelier programmation AgiBot X2', 0, null],
            ['client@robotix.test', 'Atelier programmation Poppy', null, null],
            ['manon.leroy@exemple.fr', 'Atelier programmation Poppy', null, null],
            ['sarah.lefebvre@exemple.fr', 'Atelier : apprendre un geste à Reachy 2', null, null],
            ['nathan.robert@exemple.fr', 'Atelier : apprendre un geste à Reachy 2', null, null],
            ['lucas.bernard@exemple.fr', 'Atelier famille Poppy', null, null],
            ['chloe.richard@exemple.fr', 'Atelier famille Poppy', null, null],
        ];
        foreach ($inscriptions as [$email, $titre, $present, $travail]) {
            $this->db->table('Inscription')->insert([
                'idMembre' => $adherents[$email], 'idEvenement' => $evenements[$titre],
                'dateInscription' => $this->d('2026-09-01T10:00:00'), 'present' => $present, 'travailRealise' => $travail,
            ]);
        }

        // Réunions internes : convocations des animateurs et ordre du jour ordonné
        $reunions = [
            ['2026-09-30T18:00:00', 'Bilan des ateliers de septembre', 'Lyon', ['karim', 'ines', 'theo'], [
                'Bilan de fréquentation des ateliers', 'Retours des membres sur Poppy', 'Planning des démonstrations d\'octobre',
            ]],
            ['2026-11-03T18:00:00', 'Préparation des ateliers de Noël', 'Paris', ['karim', 'theo'], [
                'Programme de l\'atelier famille Poppy', 'Matériel à commander', 'Répartition des remplacements', 'Questions diverses',
            ]],
        ];
        foreach ($reunions as [$date, $objet, $ville, $convoques, $points]) {
            $this->db->table('Reunion')->insert(['dateReunion' => $this->d($date), 'objet' => $objet, 'idShowroom' => $showrooms[$ville]]);
            $idReunion = (int) $this->db->insertID();
            foreach ($convoques as $animateur) {
                $this->db->table('Convocation')->insert(['idReunion' => $idReunion, 'idAnimateur' => $ids[$animateur], 'dateEnvoi' => $this->d('2026-09-20T09:00:00')]);
            }
            foreach ($points as $index => $libelle) {
                $this->db->table('PointOrdreJour')->insert(['idReunion' => $idReunion, 'numOrdre' => $index + 1, 'libelle' => $libelle]);
            }
        }
    }

    /** Date du jeu d'essai décalée pour rester actuelle. */
    private function d(string $iso): string
    {
        return $this->cal->date($iso);
    }
}
