<?php

use Tests\Support\RobotixTestCase;

/**
 * AP3 : page « membre de l'organisation » — CRUD des animateurs avec remplacement,
 * événements (type, animateur, remplacement) et rapports SQL Server.
 */
final class AdminClubTest extends RobotixTestCase
{
    private function admin(): array
    {
        return $this->sessionDe('admin@robotix.test');
    }

    private function idEvenement(string $titre): int
    {
        return (int) $this->db->table('Evenement')->where('titre', $titre)->get()->getRow('idEvenement');
    }

    private function remplacantDe(string $email): ?int
    {
        $valeur = $this->db->table('Animateur')->where('idUtilisateur', $this->idUtilisateur($email))->get()->getRow('idRemplacant');

        return $valeur === null ? null : (int) $valeur;
    }

    // ---------- Animateurs et remplacement ----------

    public function testListeDesAnimateursReserveeALOrganisation(): void
    {
        $this->withSession($this->sessionDe('karim.haddad@robotix.test'))->get('admin/club/animateurs')
            ->assertSessionHas('erreur', 'Accès réservé aux administrateurs.');

        $resultat = $this->withSession($this->admin())->get('admin/club/animateurs');
        $resultat->assertOK();
        $resultat->assertSee('Karim Haddad');
        $resultat->assertSee('Inès Fontaine');
        $resultat->assertSee('Programmation des robots');
    }

    public function testUnClientDevientAnimateurAvecUnRemplacant(): void
    {
        $jules = $this->idUtilisateur('jules.moreau@exemple.fr');

        $this->withSession($this->admin())->post('admin/club/animateurs', [
            'idUtilisateur' => (string) $jules, 'specialite' => 'Robots premium',
            'idRemplacant' => (string) $this->idUtilisateur('karim.haddad@robotix.test'),
        ])->assertSessionHas('succes');

        $this->assertSame('Robots premium', $this->db->table('Animateur')->where('idUtilisateur', $jules)->get()->getRow('specialite'));
        $this->assertSame($this->idUtilisateur('karim.haddad@robotix.test'), $this->remplacantDe('jules.moreau@exemple.fr'));
    }

    public function testUnAdministrateurNePeutPasDevenirAnimateur(): void
    {
        $this->withSession($this->admin())->post('admin/club/animateurs', [
            'idUtilisateur' => (string) $this->idUtilisateur('admin@robotix.test'), 'specialite' => 'Robots', 'idRemplacant' => '',
        ])->assertSessionHas('erreur', 'Seul un client du club peut devenir animateur.');
    }

    public function testChangerDeRemplacant(): void
    {
        $karim = $this->idUtilisateur('karim.haddad@robotix.test');
        $theo  = $this->idUtilisateur('theo.garnier@robotix.test');

        $this->withSession($this->admin())->post("admin/club/animateurs/{$karim}", [
            'specialite' => 'Programmation Python', 'idRemplacant' => (string) $theo,
        ])->assertSessionHas('succes');

        $this->assertSame($theo, $this->remplacantDe('karim.haddad@robotix.test'));
    }

    public function testUnAnimateurNeSeRemplacePasLuiMeme(): void
    {
        $karim = $this->idUtilisateur('karim.haddad@robotix.test');

        $this->withSession($this->admin())->post("admin/club/animateurs/{$karim}", [
            'specialite' => 'Programmation', 'idRemplacant' => (string) $karim,
        ])->assertSessionHas('erreur', 'Un animateur ne peut pas être son propre remplaçant.');
    }

    public function testFormulaireDeModificationPrerempli(): void
    {
        $karim = $this->idUtilisateur('karim.haddad@robotix.test');
        $ines  = $this->idUtilisateur('ines.fontaine@robotix.test');

        $resultat = $this->withSession($this->admin())->get("admin/club/animateurs/{$karim}/modifier");
        $resultat->assertSee('value="Programmation des robots"');
        $resultat->assertSee('<option value="' . $ines . '" selected>');
    }

    public function testSuppressionDUnAnimateur(): void
    {
        $karim = $this->idUtilisateur('karim.haddad@robotix.test');

        $this->withSession($this->admin())->post("admin/club/animateurs/{$karim}/supprimer")->assertSessionHas('succes');

        $this->assertSame(0, $this->db->table('Animateur')->where('idUtilisateur', $karim)->countAllResults());
        $this->assertSame(0, $this->db->table('Evenement')->where('idAnimateur', $karim)->countAllResults());
    }

    // ---------- Événements ----------

    public function testLeFormulaireDEvenementProposeTypesEtAnimateurs(): void
    {
        $resultat = $this->withSession($this->admin())->get('admin/evenements/nouveau');

        $resultat->assertSee('<option value="atelier">Atelier</option>');
        $resultat->assertSee('name="idAnimateur"');
        $resultat->assertSee('Karim Haddad');
    }

    public function testCreationDUnEvenementAvecSonAnimateur(): void
    {
        $theo = $this->idUtilisateur('theo.garnier@robotix.test');

        $this->withSession($this->admin())->post('admin/evenements', [
            'titre' => 'Atelier de Pâques', 'description' => '', 'type' => 'atelier',
            'dateDebut' => $this->demo('2027-03-28T14:00'), 'dateFin' => $this->demo('2027-03-28T16:00'),
            'idShowroom' => '', 'idProduit' => '', 'idAnimateur' => (string) $theo,
        ])->assertSessionHas('succes');

        $this->assertSame($theo, (int) $this->db->table('Evenement')->where('titre', 'Atelier de Pâques')->get()->getRow('idAnimateur'));
    }

    public function testTypeInconnuRefuse(): void
    {
        $this->withSession($this->admin())->post('admin/evenements', [
            'titre' => 'Concert', 'type' => 'concert', 'dateDebut' => $this->demo('2027-03-28T14:00'), 'dateFin' => $this->demo('2027-03-28T16:00'),
        ])->assertSessionHas('erreur');
    }

    public function testFaireRemplacerLAnimateurDUnEvenement(): void
    {
        $evenement = $this->idEvenement('Atelier famille Poppy'); // Karim, remplacé par Inès

        $this->withSession($this->admin())->post("admin/evenements/{$evenement}/remplacer")->assertSessionHas('succes');

        $this->assertSame($this->idUtilisateur('ines.fontaine@robotix.test'), (int) $this->db->table('Evenement')->where('idEvenement', $evenement)->get()->getRow('idAnimateur'));
    }

    public function testLePlanningAfficheLAnimateur(): void
    {
        $resultat = $this->withSession($this->admin())->get('admin/evenements');

        $resultat->assertSee('Karim Haddad');
        $resultat->assertSee('Faire remplacer');
    }

    // ---------- Rapports SQL Server ----------

    public function testRapportsDesProceduresVuesEtMails(): void
    {
        $resultat = $this->withSession($this->admin())->get('admin/club/rapports', [
            'annee' => $this->anneeDemo(2026), 'date' => $this->demo('2026-09-30'), 'idAdherent' => $this->idUtilisateur('client@robotix.test'),
            'idMembre' => $this->idUtilisateur('lucas.bernard@exemple.fr'), 'debut' => $this->demo('2026-09-01'), 'fin' => $this->demo('2026-09-30'),
        ]);

        $resultat->assertOK();
        $resultat->assertSee('ps_AdherentsRenouveles');
        $resultat->assertSee('Durand');                                   // adhérent renouvelé
        $resultat->assertSee('Bilan de fréquentation des ateliers');      // ordre du jour
        $resultat->assertSee('A programmé un salut de la tête en Python.'); // événements suivis
        $resultat->assertSee('<strong id="nb-evenements">2</strong>');    // paramètre OUTPUT
        $resultat->assertSee('5,00 h');                                   // heures d'entraînement
        $resultat->assertSee('Entraîneur');                               // vue v_AdherentsRoles
        $resultat->assertSee('Inscription confirmée');                    // mails générés
    }
}
