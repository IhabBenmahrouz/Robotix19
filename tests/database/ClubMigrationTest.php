<?php

use Tests\Support\RobotixTestCase;

final class ClubMigrationTest extends RobotixTestCase
{
    public function testLesTablesDuClubExistent(): void
    {
        foreach (['CategorieAge', 'Reduction', 'Tarif', 'Adhesion', 'ClientInteret', 'Panier'] as $table) {
            $this->assertTrue($this->db->tableExists($table, false), "Table $table absente");
        }
    }

    public function testLesColonnesAjoutees(): void
    {
        $client = $this->db->getFieldNames('Client');
        foreach (['dateNaissance', 'photo', 'idCategorieAge'] as $colonne) {
            $this->assertContains($colonne, $client);
        }
        $this->assertContains('nbPlaces', $this->db->getFieldNames('Evenement'));
    }

    public function testUneSeuleAdhesionParClientEtParAnnee(): void
    {
        $adhesion = $this->pdo->ligne('SELECT TOP 1 idUtilisateur, annee, idTarif FROM Adhesion');

        // PDO (ERRMODE_EXCEPTION) lève toujours une exception sur une violation de contrainte
        $this->expectException(PDOException::class);
        $this->pdo->executer(
            'INSERT INTO Adhesion (idUtilisateur, annee, idTarif, montant) VALUES (:utilisateur, :annee, :tarif, 10)',
            ['utilisateur' => $adhesion['idUtilisateur'], 'annee' => $adhesion['annee'], 'tarif' => $adhesion['idTarif']],
        );
    }
}
