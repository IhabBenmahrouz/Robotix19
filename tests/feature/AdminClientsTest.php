<?php

use Tests\Support\RobotixTestCase;

final class AdminClientsTest extends RobotixTestCase
{
    private function admin(): array
    {
        return $this->sessionDe('admin@robotix.test');
    }

    private function idCategorieAge(string $libelle): int
    {
        return (int) $this->db->table('CategorieAge')->where('libelle', $libelle)->get()->getRow('idCategorieAge');
    }

    public function testReserveAuxAdministrateurs(): void
    {
        $this->withSession($this->sessionDe('client@robotix.test'))->get('admin/clients')
            ->assertSessionHas('erreur', 'Accès réservé aux administrateurs.');
    }

    public function testListeGlobaleAvecListeDeroulanteAlimenteeParLaBase(): void
    {
        $resultat = $this->withSession($this->admin())->get('admin/clients');

        $resultat->assertOK();
        $resultat->assertSee('15 adhérent(s)');
        $resultat->assertSee('Lefebvre');
        foreach (['Jeune', 'Adulte', 'Senior'] as $libelle) {
            $resultat->assertSee('<option value="' . $this->idCategorieAge($libelle) . '">' . $libelle . '</option>');
        }
    }

    public function testListeFiltreeParCategorie(): void
    {
        $resultat = $this->withSession($this->admin())->get('admin/clients', ['categorie' => $this->idCategorieAge('Jeune')]);

        $resultat->assertSee('3 adhérent(s)');
        $resultat->assertSee('Petit');
        $resultat->assertSee('Leroy');
        $resultat->assertDontSee('Lefebvre');
        $resultat->assertSee('<option value="' . $this->idCategorieAge('Jeune') . '" selected>Jeune</option>');
    }

    public function testMiseAJourRecalculeLaCategorie(): void
    {
        $id = $this->idUtilisateur('client@robotix.test');

        $resultat = $this->withSession($this->admin())->post("admin/clients/{$id}", [
            'nom' => 'Martin', 'prenom' => 'Camille', 'email' => 'client@robotix.test',
            'telephone' => '07 00 00 00 01', 'dateNaissance' => '2005-01-01', 'actif' => '1',
        ]);

        $resultat->assertSessionHas('succes');
        $client = $this->db->table('Client')->where('idUtilisateur', $id)->get()->getRowArray();
        $this->assertSame('07 00 00 00 01', $client['telephone']);
        $this->assertSame($this->idCategorieAge('Jeune'), (int) $client['idCategorieAge']);
    }

    public function testEmailDejaPrisRefuse(): void
    {
        $id = $this->idUtilisateur('client@robotix.test');

        $this->withSession($this->admin())->post("admin/clients/{$id}", [
            'nom' => 'Martin', 'prenom' => 'Camille', 'email' => 'emma.petit@exemple.fr',
            'telephone' => '06 12 34 56 78', 'dateNaissance' => '1994-03-12', 'actif' => '1',
        ])->assertSessionHas('erreur', 'Cet e-mail est déjà utilisé par un autre compte.');
    }

    public function testFormulaireDeModificationPrerempli(): void
    {
        $id = $this->idUtilisateur('client@robotix.test');

        $this->withSession($this->admin())->get("admin/clients/{$id}/modifier")
            ->assertSee('value="1994-03-12"');
    }

    public function testSuppressionDUnAdherent(): void
    {
        $id = $this->idUtilisateur('paul.laurent@exemple.fr');

        $this->withSession($this->admin())->post("admin/clients/{$id}/supprimer")->assertSessionHas('succes');

        $this->assertSame(0, $this->db->table('Utilisateur')->where('idUtilisateur', $id)->countAllResults());
        $this->assertSame(0, $this->db->table('Adhesion')->where('idUtilisateur', $id)->countAllResults());
    }

    public function testSuppressionRefuseeSiLeClientACommande(): void
    {
        $id      = $this->idUtilisateur('client@robotix.test');
        $adresse = (int) $this->db->table('Adresse')->where('idUtilisateur', $id)->get()->getRow('idAdresse');
        $this->db->table('Commande')->set('dateCommande', 'GETDATE()', false)->insert([
            'statutCourant' => 'payee', 'montantTotal' => 100, 'idAdresseLivraison' => $adresse,
            'idAdresseFacturation' => $adresse, 'idUtilisateur' => $id,
        ]);

        $this->withSession($this->admin())->post("admin/clients/{$id}/supprimer")
            ->assertSessionHas('erreur', 'Ce client a passé des commandes : désactivez son compte au lieu de le supprimer.');
        $this->assertSame(1, $this->db->table('Utilisateur')->where('idUtilisateur', $id)->countAllResults());
    }
}
