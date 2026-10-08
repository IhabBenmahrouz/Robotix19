<?php

use App\Libraries\CalendrierDemo;
use Tests\Support\RobotixTestCase;

final class AdminDemoTest extends RobotixTestCase
{
    public function testLaPageDecritLEtatDeLaDemo(): void
    {
        $resultat = $this->withSession($this->sessionDe('admin@robotix.test'))->get('admin/demo');

        $resultat->assertOK();
        $resultat->assertSee('Jeu de démonstration');
        $resultat->assertSee('Dernière réinitialisation');
        $resultat->assertSee('name="confirmation"');
    }

    public function testReserveeALAdministrateur(): void
    {
        $this->withSession($this->sessionDe('client@robotix.test'))->get('admin/demo')->assertRedirect();
        $this->post('admin/demo/reinitialiser', ['confirmation' => 'REINITIALISER'])->assertRedirect();
        $this->assertSame(1, $this->db->table('Utilisateur')->where('email', 'admin@robotix.test')->countAllResults());
    }

    public function testSansConfirmationRienNeChange(): void
    {
        $this->db->table('MessageContact')->insert(['nom' => 'Témoin', 'email' => 'temoin@exemple.fr', 'objet' => 'autre', 'message' => 'Ce message doit rester.']);

        $this->withSession($this->sessionDe('admin@robotix.test'))
            ->post('admin/demo/reinitialiser', ['confirmation' => 'oui'])
            ->assertSessionHas('erreur');

        $this->assertSame(1, $this->db->table('MessageContact')->where('nom', 'Témoin')->countAllResults());
    }

    public function testLaReinitialisationRecreeLeJeuDEssai(): void
    {
        $this->db->table('MessageContact')->insert(['nom' => 'Témoin', 'email' => 'temoin@exemple.fr', 'objet' => 'autre', 'message' => 'Ce message doit disparaître.']);

        $resultat = $this->withSession($this->sessionDe('admin@robotix.test'))
            ->post('admin/demo/reinitialiser', ['confirmation' => 'REINITIALISER']);

        $resultat->assertSessionHas('succes');
        $this->assertSame(0, $this->db->table('MessageContact')->where('nom', 'Témoin')->countAllResults());
        $this->assertSame(CalendrierDemo::semainesDepuisReference(date('Y-m-d')), CalendrierDemo::courant()->semaines());

        // La session pointe sur l'administrateur recréé, et l'action est journalisée
        $admin = $this->idUtilisateur('admin@robotix.test');
        $this->assertSame($admin, session('idUtilisateur'));
        $this->assertSame(1, $this->db->table('Journal')->where(['typeAction' => 'reinitialisation', 'idUtilisateur' => $admin])->countAllResults());
    }
}
