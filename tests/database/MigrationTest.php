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
