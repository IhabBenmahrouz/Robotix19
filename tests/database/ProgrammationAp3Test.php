<?php

use App\Libraries\ProceduresAp3;
use Tests\Support\RobotixTestCase;

/**
 * Programmation SQL Server de l'AP3 : 5 procédures stockées, 3 déclencheurs, 2 vues.
 */
final class ProgrammationAp3Test extends RobotixTestCase
{
    private ProceduresAp3 $ps;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ps = new ProceduresAp3($this->pdo);
    }

    private function id(string $email): int
    {
        return (int) $this->pdo->valeur('SELECT idUtilisateur FROM Utilisateur WHERE email = :email', ['email' => $email]);
    }

    private function idEvenement(string $titre): int
    {
        return (int) $this->pdo->valeur('SELECT idEvenement FROM Evenement WHERE titre = :titre', ['titre' => $titre]);
    }

    private function nbEvenements(string $email): int
    {
        return (int) $this->pdo->valeur('SELECT nbEvenements FROM Membre WHERE idUtilisateur = :id', ['id' => $this->id($email)]);
    }

    // ---------- Procédures stockées ----------

    public function testAdherentsAyantRenouveleLeurAdhesion(): void
    {
        $lignes = $this->ps->adherentsRenouveles($this->anneeDemo(2026));

        $this->assertSame(['Durand', 'Martin', 'Michel', 'Petit', 'Robert'], array_column($lignes, 'nom'));
        $emma = $lignes[array_search('Petit', array_column($lignes, 'nom'), true)];
        $this->assertSame(['Découverte', 'Passion'], [$emma['formulePrecedente'], $emma['formuleAnnee']]);
        $this->assertSame([], $this->ps->adherentsRenouveles($this->anneeDemo(2025)));
    }

    public function testOrdreDuJourDUneReunionAUneDate(): void
    {
        $points = $this->ps->ordreDuJour($this->demo('2026-09-30'));

        $this->assertSame([1, 2, 3], array_column($points, 'numOrdre'));
        $this->assertSame('Bilan de fréquentation des ateliers', $points[0]['libelle']);
        $this->assertSame('18:00', $points[0]['heure']);
        $this->assertCount(4, $this->ps->ordreDuJour($this->demo('2026-11-03')));
        $this->assertSame([], $this->ps->ordreDuJour($this->demo('2026-01-01')));
    }

    public function testEvenementsSuivisParUnAdherent(): void
    {
        $lignes = $this->ps->evenementsSuivis($this->id('client@robotix.test'));

        $this->assertSame(['Atelier découverte Poppy', 'Démonstration de rentrée Figure 02', 'Atelier programmation Poppy'], array_column($lignes, 'titre'));
        $this->assertSame(['Présent', 'Présent', 'Non pointé'], array_column($lignes, 'presence'));
        $this->assertSame(['Martin', 'Camille'], [$lignes[0]['nom'], $lignes[0]['prenom']]);
        $this->assertSame('A programmé un salut de la tête en Python.', $lignes[0]['travailRealise']);
        $this->assertSame('Absent', $this->ps->evenementsSuivis($this->id('emma.petit@exemple.fr'))[0]['presence']);
    }

    public function testNombreDEvenementsSuivisEntreDeuxDates(): void
    {
        $this->assertSame(2, $this->ps->nbEvenementsEntreDates($this->id('lucas.bernard@exemple.fr'), $this->demo('2026-09-01'), $this->demo('2026-09-30')));
        $this->assertSame(1, $this->ps->nbEvenementsEntreDates($this->id('emma.petit@exemple.fr'), $this->demo('2026-09-01'), $this->demo('2026-09-30'))); // absente le 9
        $this->assertSame(1, $this->ps->nbEvenementsEntreDates($this->id('client@robotix.test'), $this->demo('2026-09-10'), $this->demo('2026-09-30')));
        $this->assertSame(0, $this->ps->nbEvenementsEntreDates($this->id('chloe.richard@exemple.fr'), $this->demo('2026-01-01'), $this->demo('2026-12-31')));
    }

    public function testHeuresDEntrainementParJoueur(): void
    {
        $heures = array_column($this->ps->heuresEntrainement(), 'heures', 'prenom');

        $this->assertCount(9, $heures);
        $this->assertSame(5.0, (float) $heures['Lucas']);       // 2 h + 3 h d'atelier
        $this->assertSame(2.0, (float) $heures['Camille']);     // la démonstration ne compte pas
        $this->assertSame(0.0, (float) $heures['Sarah']);       // absente
        $this->assertSame('Lucas', array_key_first($heures));
    }

    // ---------- Déclencheurs ----------

    public function testUneInscriptionGenereUnMail(): void
    {
        $avant = (int) $this->pdo->valeur('SELECT COUNT(*) FROM MailAEnvoyer');

        $this->pdo->executer('INSERT INTO Inscription (idMembre, idEvenement) VALUES (:m, :e)', [
            'm' => $this->id('chloe.richard@exemple.fr'), 'e' => $this->idEvenement('Démonstration Figure 02'),
        ]);

        $mail = $this->pdo->ligne('SELECT TOP 1 * FROM MailAEnvoyer ORDER BY idMail DESC');
        $this->assertSame($avant + 1, (int) $this->pdo->valeur('SELECT COUNT(*) FROM MailAEnvoyer'));
        $this->assertSame('chloe.richard@exemple.fr', $mail['destinataire']);
        $this->assertSame('Inscription confirmée : Démonstration Figure 02', $mail['objet']);
        $this->assertStringContainsString($this->demoFr('2026-12-17'), $mail['corps']);
    }

    public function testInsertionsMultiples(): void
    {
        $avant = (int) $this->pdo->valeur('SELECT COUNT(*) FROM MailAEnvoyer');
        $chloe = $this->nbEvenements('chloe.richard@exemple.fr');

        $this->pdo->executer(
            "INSERT INTO Inscription (idMembre, idEvenement)
             SELECT m.idUtilisateur, :e FROM Membre m JOIN Utilisateur u ON u.idUtilisateur = m.idUtilisateur
             WHERE u.email IN ('chloe.richard@exemple.fr', 'helene.michel@exemple.fr')",
            ['e' => $this->idEvenement('Démonstration AgiBot X2')],
        );

        $this->assertSame($avant + 2, (int) $this->pdo->valeur('SELECT COUNT(*) FROM MailAEnvoyer'));
        $this->assertSame($chloe + 1, $this->nbEvenements('chloe.richard@exemple.fr'));
    }

    public function testLeNombreDEvenementsSuitLesInscriptions(): void
    {
        $this->assertSame(3, $this->nbEvenements('client@robotix.test'));

        $this->pdo->executer('DELETE FROM Inscription WHERE idMembre = :m AND idEvenement = :e', [
            'm' => $this->id('client@robotix.test'), 'e' => $this->idEvenement('Atelier programmation Poppy'),
        ]);
        $this->assertSame(2, $this->nbEvenements('client@robotix.test'));

        $this->pdo->executer('UPDATE Inscription SET present = 1 WHERE idMembre = :m', ['m' => $this->id('client@robotix.test')]);
        $this->assertSame(2, $this->nbEvenements('client@robotix.test'));
    }

    public function testHistorisationDUneAdhesionNonRenouvelee(): void
    {
        $this->pdo->executer("UPDATE Utilisateur SET actif = 0 WHERE email IN ('jules.moreau@exemple.fr', 'client@robotix.test')");

        $historique = $this->pdo->lignes('SELECT * FROM HistoriqueAdhesion');
        $this->assertCount(1, $historique); // Camille a adhéré cette année : pas d'historique
        $this->assertSame('Moreau', $historique[0]['nom']);
        $this->assertSame($this->anneeDemo(2025), (int) $historique[0]['derniereAnnee']);
        $this->assertSame('Adhésion non renouvelée', $historique[0]['motif']);
    }

    public function testPasDHistoriqueSansChangementDEtat(): void
    {
        $this->pdo->executer("UPDATE Utilisateur SET nom = nom WHERE email = 'jules.moreau@exemple.fr'");

        $this->assertSame(0, (int) $this->pdo->valeur('SELECT COUNT(*) FROM HistoriqueAdhesion'));
    }

    // ---------- Vues ----------

    public function testVueDesEvenementsAvecLesPresents(): void
    {
        $this->assertSame(8, (int) $this->pdo->valeur('SELECT COUNT(*) FROM v_EvenementsPresents'));
        $this->assertSame(3, (int) $this->pdo->valeur("SELECT COUNT(*) FROM v_EvenementsPresents WHERE titre = 'Atelier programmation AgiBot X2'"));
    }

    public function testVueDesAdherentsEntraineursEtJoueurs(): void
    {
        $roles = $this->pdo->lignes('SELECT role, COUNT(*) AS nb FROM v_AdherentsRoles GROUP BY role ORDER BY role');

        $this->assertSame([['role' => 'Entraîneur', 'nb' => 3], ['role' => 'Joueur', 'nb' => 9]], $roles);
        $this->assertSame('Programmation des robots', $this->pdo->valeur("SELECT detail FROM v_AdherentsRoles WHERE prenom = 'Karim'"));
    }
}
