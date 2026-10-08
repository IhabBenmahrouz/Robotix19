<?php

use Tests\Support\RobotixTestCase;

final class Ap3MigrationTest extends RobotixTestCase
{
    public function testLesTablesDeLAp3Existent(): void
    {
        foreach (['Membre', 'Animateur', 'TypeEvenement', 'Inscription', 'Reunion', 'Convocation', 'PointOrdreJour', 'MailAEnvoyer', 'HistoriqueAdhesion'] as $table) {
            $this->assertTrue($this->db->tableExists($table, false), "Table $table absente");
        }
        $this->assertContains('pseudo', $this->db->getFieldNames('Utilisateur'));
        $this->assertContains('idAnimateur', $this->db->getFieldNames('Evenement'));
    }

    public function testUnAnimateurNePeutPasSeRemplacerLuiMeme(): void
    {
        $id = (int) $this->pdo->valeur('SELECT TOP 1 idUtilisateur FROM Animateur');

        $this->expectException(PDOException::class);
        $this->pdo->executer('UPDATE Animateur SET idRemplacant = :a WHERE idUtilisateur = :b', ['a' => $id, 'b' => $id]);
    }

    public function testLeTypeDEvenementEstUneCleEtrangere(): void
    {
        $this->expectException(PDOException::class);
        $this->pdo->executer("UPDATE Evenement SET type = 'concert' WHERE idEvenement = (SELECT TOP 1 idEvenement FROM Evenement)");
    }

    public function testLePseudoEstUnique(): void
    {
        $this->expectException(PDOException::class);
        $this->pdo->executer("UPDATE Utilisateur SET pseudo = 'camille' WHERE email = 'emma.petit@exemple.fr'");
    }

    public function testUneInscriptionParMembreEtParEvenement(): void
    {
        $ligne = $this->pdo->ligne('SELECT TOP 1 idMembre, idEvenement FROM Inscription');

        $this->expectException(PDOException::class);
        $this->pdo->executer('INSERT INTO Inscription (idMembre, idEvenement) VALUES (:m, :e)', ['m' => $ligne['idMembre'], 'e' => $ligne['idEvenement']]);
    }

    public function testLeJeuDEssai(): void
    {
        $attendus = ['Animateur' => 3, 'Membre' => 9, 'TypeEvenement' => 4, 'Reunion' => 2];
        foreach ($attendus as $table => $nombre) {
            $this->assertSame($nombre, (int) $this->pdo->valeur("SELECT COUNT(*) FROM {$table}"), "Table {$table}");
        }
        $this->assertSame(0, (int) $this->pdo->valeur('SELECT COUNT(*) FROM Evenement WHERE idAnimateur IS NULL'));
        $this->assertSame(0, (int) $this->pdo->valeur('SELECT COUNT(*) FROM Animateur WHERE idRemplacant IS NULL'));
        $this->assertGreaterThanOrEqual(3, (int) $this->pdo->valeur('SELECT MIN(n) FROM (SELECT COUNT(*) n FROM PointOrdreJour GROUP BY idReunion) t'));
        $this->assertGreaterThan(0, (int) $this->pdo->valeur('SELECT COUNT(*) FROM Inscription WHERE present = 1 AND travailRealise IS NOT NULL'));
        $this->assertGreaterThan(0, (int) $this->pdo->valeur('SELECT COUNT(*) FROM Inscription WHERE present = 0'));
    }
}
