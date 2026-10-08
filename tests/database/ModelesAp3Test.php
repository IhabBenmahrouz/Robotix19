<?php

use App\Models\AnimateurModel;
use App\Models\InscriptionModel;
use App\Models\MailModel;
use App\Models\MembreModel;
use App\Models\ReunionModel;
use App\Models\TypeEvenementModel;
use App\Models\VueAdherentsRolesModel;
use App\Models\VueEvenementsPresentsModel;
use Tests\Support\RobotixTestCase;

/**
 * Modèles CodeIgniter de l'AP3 : mapping des tables et des vues, héritage,
 * clés étrangères et tables de relation.
 */
final class ModelesAp3Test extends RobotixTestCase
{
    private function id(string $email): int
    {
        return $this->idUtilisateur($email);
    }

    private function idEvenement(string $titre): int
    {
        return (int) $this->db->table('Evenement')->where('titre', $titre)->get()->getRow('idEvenement');
    }

    // ---------- Mapping des tables et des vues ----------

    public function testTypesDEvenement(): void
    {
        $this->assertSame(['atelier' => 'Atelier', 'demo' => 'Démonstration', 'lancement' => 'Lancement', 'salon' => 'Salon'], model(TypeEvenementModel::class)->liste());
    }

    public function testVuesEnLectureSeule(): void
    {
        $this->assertSame(9, model(VueAdherentsRolesModel::class)->where('role', 'Joueur')->countAllResults());
        $this->assertCount(8, model(VueEvenementsPresentsModel::class)->findAll());
        $this->assertSame(3, count(model(VueEvenementsPresentsModel::class)->parEvenement()['Atelier programmation AgiBot X2']));
    }

    public function testMailsGeneres(): void
    {
        $mails = model(MailModel::class)->derniers(5);

        $this->assertCount(5, $mails);
        $this->assertStringStartsWith('Inscription confirmée', $mails[0]['objet']);
    }

    // ---------- Héritage ----------

    public function testUnMembreReunitUtilisateurClientEtMembre(): void
    {
        $camille = model(MembreModel::class)->complet($this->id('client@robotix.test'));

        $this->assertSame(['Martin', 'camille', '06 12 34 56 78', 'intermédiaire', 3],
            [$camille['nom'], $camille['pseudo'], $camille['telephone'], $camille['niveau'], (int) $camille['nbEvenements']]);
        $this->assertCount(9, model(MembreModel::class)->complets());
    }

    public function testUnAnimateurAvecSonRemplacant(): void
    {
        $karim = model(AnimateurModel::class)->complet($this->id('karim.haddad@robotix.test'));

        $this->assertSame('Programmation des robots', $karim['specialite']);
        $this->assertSame('Inès Fontaine', $karim['remplacant']);
    }

    public function testSeulUnClientPeutDevenirMembre(): void
    {
        $this->expectException(InvalidArgumentException::class);
        model(MembreModel::class)->devenirMembre($this->id('admin@robotix.test'));
    }

    public function testUnClientDevientMembre(): void
    {
        model(MembreModel::class)->devenirMembre($this->id('jules.moreau@exemple.fr'), 'confirmé');

        $this->assertSame('confirmé', model(MembreModel::class)->complet($this->id('jules.moreau@exemple.fr'))['niveau']);
    }

    // ---------- Réflexivité et clés étrangères ----------

    public function testUnAnimateurNePeutPasSeRemplacerLuiMeme(): void
    {
        $this->expectException(InvalidArgumentException::class);
        model(AnimateurModel::class)->definirRemplacant($this->id('karim.haddad@robotix.test'), $this->id('karim.haddad@robotix.test'));
    }

    public function testLeRemplacantDoitEtreUnAnimateur(): void
    {
        $this->expectException(InvalidArgumentException::class);
        model(AnimateurModel::class)->definirRemplacant($this->id('karim.haddad@robotix.test'), $this->id('client@robotix.test'));
    }

    public function testSupprimerUnAnimateurConfieSesEvenementsASonRemplacant(): void
    {
        $karim = $this->id('karim.haddad@robotix.test');
        $ines  = $this->id('ines.fontaine@robotix.test');
        $theo  = $this->id('theo.garnier@robotix.test');
        $nbEvenementsKarim = $this->db->table('Evenement')->where('idAnimateur', $karim)->countAllResults();

        model(AnimateurModel::class)->supprimerAnimateur($karim);

        $this->assertNull(model(AnimateurModel::class)->complet($karim));
        $this->assertSame(0, $this->db->table('Evenement')->where('idAnimateur', $karim)->countAllResults());
        $this->assertGreaterThanOrEqual($nbEvenementsKarim, $this->db->table('Evenement')->where('idAnimateur', $ines)->countAllResults());
        $this->assertNull($this->db->table('Animateur')->where('idUtilisateur', $theo)->get()->getRow('idRemplacant')); // Théo était remplacé par Karim
        $this->assertSame(1, $this->db->table('Client')->where('idUtilisateur', $karim)->countAllResults()); // il reste client
    }

    public function testFaireRemplacerUnAnimateurSurUnEvenement(): void
    {
        $evenement = $this->idEvenement('Atelier famille Poppy'); // animé par Karim

        $nouveau = model(AnimateurModel::class)->remplacerSurEvenement($evenement);

        $this->assertSame($this->id('ines.fontaine@robotix.test'), $nouveau);
        $this->assertSame($nouveau, (int) $this->db->table('Evenement')->where('idEvenement', $evenement)->get()->getRow('idAnimateur'));
    }

    public function testEvenementsAnimesEtRemplacements(): void
    {
        $ines = model(AnimateurModel::class)->evenementsAnimes($this->id('ines.fontaine@robotix.test'));
        $roles = array_unique(array_column($ines, 'role'));
        sort($roles);

        $this->assertSame(['remplaçant', 'titulaire'], $roles);
        $this->assertTrue(model(AnimateurModel::class)->peutPointer($this->id('ines.fontaine@robotix.test'), $this->idEvenement('Atelier famille Poppy')));
        $this->assertFalse(model(AnimateurModel::class)->peutPointer($this->id('theo.garnier@robotix.test'), $this->idEvenement('Atelier famille Poppy')));
    }

    // ---------- Tables de relation ----------

    public function testInscriptionDUnMembre(): void
    {
        model(InscriptionModel::class)->inscrire($this->id('chloe.richard@exemple.fr'), $this->idEvenement('Démonstration AgiBot X2'));

        $inscrits = array_column(model(InscriptionModel::class)->inscritsDe($this->idEvenement('Démonstration AgiBot X2')), 'prenom');
        $this->assertContains('Chloé', $inscrits);
    }

    public function testDoubleInscriptionRefusee(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Vous êtes déjà inscrit à cet événement.');
        model(InscriptionModel::class)->inscrire($this->id('client@robotix.test'), $this->idEvenement('Atelier programmation Poppy'));
    }

    public function testInscriptionAUnEvenementPasseRefusee(): void
    {
        $this->expectExceptionMessage('Cet événement est passé.');
        model(InscriptionModel::class)->inscrire($this->id('chloe.richard@exemple.fr'), $this->idEvenement('Atelier découverte Poppy'));
    }

    public function testInscriptionAUnEvenementInconnuRefusee(): void
    {
        $this->expectExceptionMessage('Événement introuvable.');
        model(InscriptionModel::class)->inscrire($this->id('chloe.richard@exemple.fr'), 999999);
    }

    public function testPointageDeLaPresenceEtDuTravail(): void
    {
        $evenement = $this->idEvenement('Atelier programmation Poppy');
        model(InscriptionModel::class)->pointer($this->id('manon.leroy@exemple.fr'), $evenement, true, 'A fait danser Reachy Mini.');

        $ligne = $this->db->table('Inscription')->where(['idMembre' => $this->id('manon.leroy@exemple.fr'), 'idEvenement' => $evenement])->get()->getRowArray();
        $this->assertSame([1, 'A fait danser Reachy Mini.'], [(int) $ligne['present'], $ligne['travailRealise']]);
    }

    public function testConvocationsEtOrdreDuJour(): void
    {
        $reunions = model(ReunionModel::class)->avecDetails();

        $this->assertSame('Bilan des ateliers de septembre', $reunions[0]['objet']);
        $this->assertSame(['Haddad', 'Fontaine', 'Garnier'], array_column(model(ReunionModel::class)->convoques((int) $reunions[0]['idReunion']), 'nom'));
        $this->assertSame([1, 2, 3, 4], array_map('intval', array_column(model(ReunionModel::class)->ordreDuJour((int) $reunions[1]['idReunion']), 'numOrdre')));
    }
}
