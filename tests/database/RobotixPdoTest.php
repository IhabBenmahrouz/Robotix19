<?php

use App\Libraries\RobotixPdo;
use Tests\Support\RobotixTestCase;

final class RobotixPdoTest extends RobotixTestCase
{
    public function testLaConnexionPdoInterrogeSqlServer(): void
    {
        $this->assertSame(1, (int) $this->pdo->valeur('SELECT 1'));
        $this->assertSame('sqlsrv', $this->pdo->pdo()->getAttribute(PDO::ATTR_DRIVER_NAME));
    }

    public function testRequetePrepareeAvecParametresNommes(): void
    {
        $ligne = $this->pdo->ligne('SELECT nom FROM Produit WHERE reference = :reference', ['reference' => 'RBX-UNI-G1']);

        $this->assertSame('Unitree G1', $ligne['nom']);
        $this->assertNull($this->pdo->ligne('SELECT nom FROM Produit WHERE reference = :reference', ['reference' => "x' OR 1=1 --"]));
    }

    public function testLesAccentsSontConserves(): void
    {
        $this->pdo->executer(
            "INSERT INTO MessageContact (nom, email, objet, message) VALUES (:nom, :email, 'autre', :message)",
            ['nom' => 'Hélène Châtelet', 'email' => 'pdo@exemple.fr', 'message' => 'Message écrit avec PDO, accents compris.'],
        );

        $this->assertSame('Hélène Châtelet', $this->pdo->valeur('SELECT nom FROM MessageContact WHERE email = :email', ['email' => 'pdo@exemple.fr']));
    }

    public function testUneTransactionEchoueeAnnuleTout(): void
    {
        try {
            $this->pdo->transaction(function (RobotixPdo $pdo): void {
                $pdo->executer("INSERT INTO MessageContact (nom, email, objet, message) VALUES ('A', 'annule@exemple.fr', 'autre', 'Message qui doit disparaître.')");
                throw new RuntimeException('échec volontaire');
            });
        } catch (RuntimeException) {
        }

        $this->assertSame(0, (int) $this->pdo->valeur("SELECT COUNT(*) FROM MessageContact WHERE email = 'annule@exemple.fr'"));
    }

    public function testUneTransactionReussieRenvoieLeResultat(): void
    {
        $resultat = $this->pdo->transaction(fn (RobotixPdo $pdo) => $pdo->executer(
            "UPDATE Produit SET stock = stock WHERE reference = 'RBX-UNI-G1'",
        ));

        $this->assertSame(1, $resultat);
    }
}
