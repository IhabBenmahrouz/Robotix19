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
