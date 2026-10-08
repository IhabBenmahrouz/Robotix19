<?php

use App\Models\TarifModel;
use Tests\Support\RobotixTestCase;

final class TarifModelTest extends RobotixTestCase
{
    public function testLesOptionsOntLeFormatDuCalculateur(): void
    {
        $options = model(TarifModel::class)->options();

        $this->assertSame(['garantie', 'livraison', 'maintenance', 'formation'], array_column($options, 'code'));
        $this->assertSame('pourcentage', $options[0]['type']);
        $this->assertSame(12.0, $options[0]['valeur']);
    }

    public function testLesFormulesSontTrieesParPrix(): void
    {
        $formules = model(TarifModel::class)->formules();

        $this->assertSame(['decouverte', 'passion', 'premium'], array_column($formules, 'code'));
        $this->assertSame(99.0, $formules[1]['valeur']);
    }

    public function testUnTarifDesactiveDisparait(): void
    {
        $this->db->table('Tarif')->where('code', 'formation')->update(['actif' => 0]);

        $this->assertNotContains('formation', array_column(model(TarifModel::class)->options(), 'code'));
    }

    public function testUnPrixModifieEnBaseApparaitSurLaPageTarifs(): void
    {
        $this->db->table('Tarif')->where('code', 'livraison')->update(['valeur' => 333]);

        $this->get('tarifs')->assertSee('333,00 €');
    }
}
