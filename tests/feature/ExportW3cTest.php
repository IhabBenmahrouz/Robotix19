<?php

use Tests\Support\RobotixTestCase;

/**
 * Écrit le HTML des pages protégées dans writable/w3c/ pour le script tools/w3c.ps1.
 * Lancé uniquement avec la variable d'environnement W3C_EXPORT=1.
 */
final class ExportW3cTest extends RobotixTestCase
{
    public function testExporterLesPagesProtegees(): void
    {
        if (getenv('W3C_EXPORT') !== '1') {
            $this->markTestSkipped('Export W3C non demandé (W3C_EXPORT=1).');
        }

        $dossier = WRITEPATH . 'w3c/';
        is_dir($dossier) || mkdir($dossier, 0775, true);
        $client = $this->sessionDe('client@robotix.test');
        $admin  = $this->sessionDe('admin@robotix.test');
        $jeton  = model(\App\Models\JetonMotDePasseModel::class)->creer($this->idUtilisateur('client@robotix.test'));
        $pages  = [
            'profil'       => [$client, 'compte/profil'],
            'reservations' => [$client, 'reservations'],
            'panier'       => [$client, 'reservations/panier'],
            'adherents'    => [$admin, 'admin/clients'],
            'statistiques' => [$admin, 'admin/statistiques'],
            'planning'     => [$admin, 'admin/evenements'],
            'club-membre'  => [$client, 'club/membres'],
            'club-animateur' => [$this->sessionDe('karim.haddad@robotix.test'), 'club/animateur'],
            'animateurs'   => [$admin, 'admin/club/animateurs'],
            'rapports'     => [$admin, 'admin/club/rapports'],
            'demo'         => [$admin, 'admin/demo'],
            'journal'      => [$admin, 'admin/journal'],
            'nouveau-mdp'  => [[], 'compte/mot-de-passe/' . $jeton],
        ];

        foreach ($pages as $nom => [$session, $url]) {
            $corps = $this->withSession($session)->get($url)->getBody();
            file_put_contents($dossier . $nom . '.html', $corps);
            $this->assertStringContainsStringIgnoringCase('<!doctype html>', $corps);
        }
    }
}
