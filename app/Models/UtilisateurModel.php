<?php

namespace App\Models;

use CodeIgniter\Model;

class UtilisateurModel extends Model
{
    protected $table         = 'Utilisateur';
    protected $primaryKey    = 'idUtilisateur';
    protected $returnType    = 'array';
    protected $allowedFields = ['nom', 'prenom', 'email', 'motDePasse', 'actif'];

    public function trouverParEmail(string $email): ?array
    {
        return $this->where('email', strtolower(trim($email)))->first();
    }

    /** Connexion par e-mail ou par pseudo (comparaison insensible à la casse, collation SQL Server). */
    public function trouverParIdentifiant(string $identifiant): ?array
    {
        $identifiant = trim($identifiant);
        if ($identifiant === '') {
            return null;
        }

        return $this->groupStart()
            ->where('email', strtolower($identifiant))
            ->orWhere('pseudo', $identifiant)
            ->groupEnd()
            ->first();
    }

    /** @return list<string> profils du Club Robotix : « membre » et/ou « animateur » */
    public function profilsClub(int $id): array
    {
        $profils = [];
        foreach (['Membre' => 'membre', 'Animateur' => 'animateur'] as $table => $profil) {
            if ($this->db->table($table)->where('idUtilisateur', $id)->countAllResults() > 0) {
                $profils[] = $profil;
            }
        }

        return $profils;
    }

    /** Le rôle dépend de la table spécialisée dans laquelle figure l'utilisateur. */
    public function role(int $id): string
    {
        foreach (['Administrateur' => 'admin', 'Redacteur' => 'redacteur', 'Client' => 'client'] as $table => $role) {
            if ($this->db->table($table)->where('idUtilisateur', $id)->countAllResults() > 0) {
                return $role;
            }
        }

        return 'visiteur';
    }
}
