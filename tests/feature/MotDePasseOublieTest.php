<?php

use App\Models\JetonMotDePasseModel;
use Tests\Support\RobotixTestCase;

final class MotDePasseOublieTest extends RobotixTestCase
{
    private const CONFIRMATION = 'Si un compte actif correspond à cette adresse, un lien de réinitialisation valable une heure vient de lui être envoyé.';

    private function nbMails(string $email): int
    {
        return $this->db->table('MailAEnvoyer')->where('destinataire', $email)->like('objet', 'Réinitialisation')->countAllResults();
    }

    /** Jeton en clair extrait du dernier mail envoyé à cette adresse. */
    private function jetonDuMail(string $email): string
    {
        $corps = $this->db->table('MailAEnvoyer')->where('destinataire', $email)->orderBy('idMail', 'DESC')->get()->getRow('corps');
        preg_match('~compte/mot-de-passe/([0-9a-f]{64})~', $corps, $m);

        return $m[1];
    }

    public function testLeLienEstProposeSurLaPageDeConnexion(): void
    {
        $this->get('compte/connexion')->assertSee('compte/mot-de-passe-oublie');
        $this->get('compte/mot-de-passe-oublie')->assertSee('Recevoir un lien');
    }

    public function testUnMailAvecUnLienEstDeposeEtSeulLEmpreinteEstStockee(): void
    {
        $this->post('compte/mot-de-passe-oublie', ['email' => ' Client@Robotix.test '])->assertSessionHas('succes', self::CONFIRMATION);

        $this->assertSame(1, $this->nbMails('client@robotix.test'));
        $jeton = $this->jetonDuMail('client@robotix.test');
        $this->assertSame(0, $this->db->table('JetonMotDePasse')->where('empreinte', $jeton)->countAllResults());
        $this->assertSame(1, $this->db->table('JetonMotDePasse')->where('empreinte', JetonMotDePasseModel::empreinte($jeton))->countAllResults());
    }

    public function testMemeReponsePourUneAdresseInconnue(): void
    {
        $this->post('compte/mot-de-passe-oublie', ['email' => 'inconnu@exemple.fr'])->assertSessionHas('succes', self::CONFIRMATION);

        $this->assertSame(0, $this->nbMails('inconnu@exemple.fr'));
    }

    public function testParcoursCompletPuisLienInutilisable(): void
    {
        $this->post('compte/mot-de-passe-oublie', ['email' => 'client@robotix.test']);
        $jeton = $this->jetonDuMail('client@robotix.test');

        $this->get("compte/mot-de-passe/$jeton")->assertSee('Nouveau mot de passe');
        $this->post("compte/mot-de-passe/$jeton", ['motDePasse' => 'Nouveau2026!', 'confirmation' => 'Nouveau2026!'])
            ->assertSessionHas('succes');

        $this->post('compte/connexion', ['identifiant' => 'client@robotix.test', 'motDePasse' => 'Nouveau2026!']);
        $this->assertSame($this->idUtilisateur('client@robotix.test'), session('idUtilisateur'));

        // Usage unique
        $this->get("compte/mot-de-passe/$jeton")->assertRedirectTo(site_url('compte/mot-de-passe-oublie'));
    }

    public function testMotDePasseTropFaibleRefuse(): void
    {
        $this->post('compte/mot-de-passe-oublie', ['email' => 'client@robotix.test']);
        $jeton = $this->jetonDuMail('client@robotix.test');

        $this->post("compte/mot-de-passe/$jeton", ['motDePasse' => 'faible', 'confirmation' => 'faible'])->assertSessionHas('erreur');

        $hash = $this->db->table('Utilisateur')->where('email', 'client@robotix.test')->get()->getRow('motDePasse');
        $this->assertTrue(password_verify('Robotix2026!', $hash));
    }

    public function testUnLienExpireOuInventeEstRefuse(): void
    {
        $this->post('compte/mot-de-passe-oublie', ['email' => 'client@robotix.test']);
        $jeton = $this->jetonDuMail('client@robotix.test');
        $this->db->query('UPDATE JetonMotDePasse SET dateExpiration = DATEADD(MINUTE, -1, GETDATE())');

        $this->get("compte/mot-de-passe/$jeton")->assertSessionHas('erreur');
        $this->get('compte/mot-de-passe/' . str_repeat('a', 64))->assertSessionHas('erreur');
        $this->post('compte/mot-de-passe/pas-un-jeton', ['motDePasse' => 'Nouveau2026!', 'confirmation' => 'Nouveau2026!'])->assertSessionHas('erreur');
    }

    public function testTroisDemandesParHeureAuPlus(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('compte/mot-de-passe-oublie', ['email' => 'client@robotix.test'])->assertSessionHas('succes');
        }

        $this->assertSame(JetonMotDePasseModel::MAX_DEMANDES_HEURE, $this->nbMails('client@robotix.test'));
    }
}
