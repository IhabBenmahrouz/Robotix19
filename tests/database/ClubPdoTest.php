<?php

use App\Models\Pdo\ClubPdo;
use Tests\Support\RobotixTestCase;

final class ClubPdoTest extends RobotixTestCase
{
    private ClubPdo $club;

    protected function setUp(): void
    {
        parent::setUp();
        $this->club = new ClubPdo($this->pdo);
    }

    private function idTarif(string $code): int
    {
        return (int) $this->pdo->valeur('SELECT idTarif FROM Tarif WHERE code = :code', ['code' => $code]);
    }

    private function inscription(array $surcharge = []): array
    {
        return $surcharge + [
            'nom' => 'Châtelet', 'prenom' => 'Hélène', 'email' => 'helene.chatelet@exemple.fr',
            'motDePasse' => 'Robotix2026!', 'telephone' => '06 98 76 54 32', 'adresse' => '5 place Bellecour',
            'codePostal' => '69002', 'ville' => 'Lyon', 'dateNaissance' => '1990-05-12',
            'idTarif' => $this->idTarif('passion'), 'interets' => [], 'photo' => null,
        ];
    }

    public function testCategoriesDAgeAvecReduction(): void
    {
        $categories = $this->club->categoriesAge();

        $this->assertSame(['Jeune', 'Adulte', 'Senior'], array_column($categories, 'libelle'));
        $this->assertSame(15.0, $categories[0]['txReduction']);
    }

    public function testCategoriePourAgeAuxBornes(): void
    {
        $this->assertNull($this->club->categoriePourAge(17));
        $this->assertSame('Jeune', $this->club->categoriePourAge(18)['libelle']);
        $this->assertSame('Jeune', $this->club->categoriePourAge(24)['libelle']);
        $this->assertSame('Adulte', $this->club->categoriePourAge(25)['libelle']);
        $this->assertSame('Senior', $this->club->categoriePourAge(60)['libelle']);
    }

    public function testMontantAvecReductionDeLaCategorie(): void
    {
        $jeune = (int) $this->club->categoriePourAge(20)['idCategorieAge'];

        $this->assertSame(84.15, $this->club->montantAdhesion($this->idTarif('passion'), $jeune));
    }

    public function testListesPourLesComposantsDuFormulaire(): void
    {
        $this->assertSame(['decouverte', 'passion', 'premium'], array_column($this->club->formules(), 'code'));
        $this->assertCount(4, $this->club->categoriesProduit());
    }

    public function testInscriptionCompleteEnUneTransaction(): void
    {
        $interets = array_column(array_slice($this->club->categoriesProduit(), 0, 2), 'idCategorie');

        $id = $this->club->inscrire($this->inscription(['interets' => $interets]));

        $utilisateur = $this->pdo->ligne('SELECT * FROM Utilisateur WHERE idUtilisateur = :id', ['id' => $id]);
        $this->assertSame('Hélène', $utilisateur['prenom']);
        $this->assertTrue(password_verify('Robotix2026!', $utilisateur['motDePasse']));

        $client = $this->pdo->ligne('SELECT ca.libelle FROM Client c JOIN CategorieAge ca ON ca.idCategorieAge = c.idCategorieAge WHERE c.idUtilisateur = :id', ['id' => $id]);
        $this->assertSame('Adulte', $client['libelle']);

        $adhesion = $this->pdo->ligne('SELECT annee, montant FROM Adhesion WHERE idUtilisateur = :id', ['id' => $id]);
        $this->assertSame((int) date('Y'), $adhesion['annee']);
        $this->assertEqualsWithDelta(99.0, (float) $adhesion['montant'], 0.001);

        $this->assertSame(2, (int) $this->pdo->valeur('SELECT COUNT(*) FROM ClientInteret WHERE idUtilisateur = :id', ['id' => $id]));
        $this->assertSame('Lyon', $this->pdo->valeur('SELECT ville FROM Adresse WHERE idUtilisateur = :id', ['id' => $id]));
    }

    public function testMoinsDe18AnsRefuseSansRienEcrire(): void
    {
        $naissance = date('Y-m-d', strtotime('-17 years'));

        try {
            $this->club->inscrire($this->inscription(['dateNaissance' => $naissance]));
            $this->fail('DomainException attendue');
        } catch (DomainException) {
        }

        $this->assertSame(0, (int) $this->pdo->valeur('SELECT COUNT(*) FROM Utilisateur WHERE email = :email', ['email' => 'helene.chatelet@exemple.fr']));
    }
}
