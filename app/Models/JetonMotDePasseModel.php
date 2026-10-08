<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Liens « mot de passe oublié » : jeton aléatoire envoyé par mail, valable une heure et une seule fois.
 * La base ne garde que l'empreinte SHA-256 du jeton : une fuite de la table ne donne aucun lien utilisable.
 */
class JetonMotDePasseModel extends Model
{
    public const DUREE_MINUTES       = 60;
    public const MAX_DEMANDES_HEURE  = 3;

    protected $table         = 'JetonMotDePasse';
    protected $primaryKey    = 'idJeton';
    protected $returnType    = 'array';
    protected $allowedFields = ['idUtilisateur', 'empreinte', 'utilise'];

    public static function empreinte(string $jeton): string
    {
        return hash('sha256', $jeton);
    }

    /** Crée un jeton pour l'utilisateur et renvoie sa valeur en clair (null si trop de demandes récentes). */
    public function creer(int $idUtilisateur): ?string
    {
        $recentes = $this->where('idUtilisateur', $idUtilisateur)
            ->where('dateCreation > DATEADD(HOUR, -1, GETDATE())', null, false)->countAllResults();
        if ($recentes >= self::MAX_DEMANDES_HEURE) {
            return null;
        }

        $jeton = bin2hex(random_bytes(32));
        $this->builder()->set(['idUtilisateur' => $idUtilisateur, 'empreinte' => self::empreinte($jeton)])
            ->set('dateExpiration', 'DATEADD(MINUTE, ' . self::DUREE_MINUTES . ', GETDATE())', false)
            ->insert();

        return $jeton;
    }

    /** Jeton valide (non utilisé, non expiré, compte actif) avec l'utilisateur, ou null. */
    public function valide(string $jeton): ?array
    {
        if (! preg_match('/^[0-9a-f]{64}$/', $jeton)) {
            return null;
        }

        return $this->select('JetonMotDePasse.idJeton, u.idUtilisateur, u.email, u.prenom')
            ->join('Utilisateur u', 'u.idUtilisateur = JetonMotDePasse.idUtilisateur')
            ->where('JetonMotDePasse.empreinte', self::empreinte($jeton))
            ->where('JetonMotDePasse.utilise', 0)
            ->where('u.actif', 1)
            ->where('JetonMotDePasse.dateExpiration > GETDATE()', null, false)
            ->first();
    }

    /** Change le mot de passe et invalide tous les jetons de l'utilisateur (en une transaction). */
    public function utiliser(array $jetonValide, string $nouveauMotDePasse): void
    {
        $this->db->transException(true)->transStart();
        $this->db->table('Utilisateur')->where('idUtilisateur', $jetonValide['idUtilisateur'])
            ->update(['motDePasse' => password_hash($nouveauMotDePasse, PASSWORD_DEFAULT)]);
        $this->builder()->where('idUtilisateur', $jetonValide['idUtilisateur'])->update(['utilise' => 1]);
        $this->db->transComplete();
        $this->db->transException(false);
    }
}
