<?php

use App\Models\Pdo\StatistiquesPdo;
use Tests\Support\RobotixTestCase;

final class StatistiquesPdoTest extends RobotixTestCase
{
    private StatistiquesPdo $stats;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stats = new StatistiquesPdo($this->pdo);
    }

    public function testMontantDesAdhesionsParAnnee(): void
    {
        $this->assertSame(985.75, $this->stats->montantAdhesions($this->anneeDemo(2026)));
        $this->assertSame(760.95, $this->stats->montantAdhesions($this->anneeDemo(2025)));
        $this->assertSame(0.0, $this->stats->montantAdhesions(2019));
    }

    public function testFonctionSansParametreSurLAnneeEnCours(): void
    {
        $this->assertSame($this->stats->montantAdhesions((int) date('Y')), $this->stats->montantTotalAdhesions());
    }

    public function testNombreEtTauxParCategorie(): void
    {
        $lignes = $this->stats->adhesionsParCategorie($this->anneeDemo(2026));

        $this->assertSame(['Jeune', 'Adulte', 'Senior'], array_column($lignes, 'libelle'));
        $this->assertSame([3, 4, 3], array_column($lignes, 'nombre'));
        $this->assertSame([30.0, 40.0, 30.0], array_column($lignes, 'taux'));
        $this->assertSame([14.3, 42.9, 42.9], array_column($this->stats->adhesionsParCategorie($this->anneeDemo(2025)), 'taux'));
    }

    public function testAnneeSansAdhesionSansDivisionParZero(): void
    {
        $lignes = $this->stats->adhesionsParCategorie(2019);

        $this->assertSame([0, 0, 0], array_column($lignes, 'nombre'));
        $this->assertSame([0.0, 0.0, 0.0], array_column($lignes, 'taux'));
    }

    public function testNombreDAdhesionsPourUneCategorieEtUneAnnee(): void
    {
        $senior = (int) $this->pdo->valeur("SELECT idCategorieAge FROM CategorieAge WHERE libelle = 'Senior'");

        $this->assertSame(3, $this->stats->nombreAdhesions($senior, $this->anneeDemo(2026)));
        $this->assertSame(3, $this->stats->nombreAdhesions($senior, $this->anneeDemo(2025)));
    }

    public function testAnneesDisponibles(): void
    {
        $annees = $this->stats->anneesDisponibles();

        $this->assertContains($this->anneeDemo(2025), $annees);
        $this->assertContains((int) date('Y'), $annees);
        $this->assertSame($annees, array_values(array_unique($annees)));
    }
}
