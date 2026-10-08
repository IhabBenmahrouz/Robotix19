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
        $this->assertFileExists(FCPATH . 'images/robots/rbx-uni-g1-1.jpg');
        $this->assertFileExists(FCPATH . 'images/robots/defaut.svg');
        $this->assertFileExists(FCPATH . 'images/marques/unitree-robotics.svg');
    }

    public function testLesVolumesAttendusSontCrees(): void
    {
        $attendus = [
            'Marque' => 10, 'Categorie' => 4, 'Produit' => 10, 'Image' => 15,
            'Showroom' => 3, 'Evenement' => 15, 'Utilisateur' => 17,
            'Client' => 15, 'Redacteur' => 1, 'Administrateur' => 1,
            'CategorieAge' => 3, 'Reduction' => 3, 'Tarif' => 7,
            'Adhesion' => 17, 'ClientInteret' => 16, 'Panier' => 2,
        ];

        foreach ($attendus as $table => $nombre) {
            $this->assertSame($nombre, $this->db->table($table)->countAllResults(), "Table $table");
        }
    }

    public function testMontantsDesAdhesionsParAnnee(): void
    {
        $somme = fn (int $annee) => (float) $this->db->table('Adhesion')->selectSum('montant')->where('annee', $annee)->get()->getRow('montant');

        $this->assertEqualsWithDelta(985.75, $somme($this->anneeDemo(2026)), 0.001);
        $this->assertEqualsWithDelta(760.95, $somme($this->anneeDemo(2025)), 0.001);
    }

    public function testLaCategorieDependDeLAge(): void
    {
        $categorie = $this->db->table('Client c')->select('ca.libelle')
            ->join('Utilisateur u', 'u.idUtilisateur = c.idUtilisateur')
            ->join('CategorieAge ca', 'ca.idCategorieAge = c.idCategorieAge')
            ->where('u.email', 'lucas.bernard@exemple.fr')->get()->getRow('libelle');

        $this->assertSame('Jeune', $categorie);
    }
}
