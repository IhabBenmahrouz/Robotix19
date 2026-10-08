<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;
use InvalidArgumentException;
use RuntimeException;

/**
 * Animateur (« adhérent entraîneur ») : spécialisation de Client.
 * Réflexivité : chaque animateur peut avoir un animateur remplaçant.
 */
class AnimateurModel extends Model
{
    protected $table            = 'Animateur';
    protected $primaryKey       = 'idUtilisateur';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = ['idUtilisateur', 'specialite', 'idRemplacant'];

    /** Reconstitue l'objet complet (Utilisateur + Client + Animateur) avec le nom du remplaçant. */
    private function heritage(): BaseBuilder
    {
        return $this->db->table('Animateur a')
            ->select("u.idUtilisateur, u.nom, u.prenom, u.email, u.pseudo, c.telephone, a.specialite, a.idRemplacant,
                      r.prenom + ' ' + r.nom AS remplacant", false)
            ->join('Client c', 'c.idUtilisateur = a.idUtilisateur')
            ->join('Utilisateur u', 'u.idUtilisateur = c.idUtilisateur')
            ->join('Utilisateur r', 'r.idUtilisateur = a.idRemplacant', 'left');
    }

    public function complets(): array
    {
        return $this->heritage()->orderBy('u.nom')->get()->getResultArray();
    }

    public function complet(int $id): ?array
    {
        return $this->heritage()->where('a.idUtilisateur', $id)->get()->getRowArray();
    }

    /** Clients qui ne sont pas encore animateurs (candidats à la création). */
    public function candidats(): array
    {
        return $this->db->table('Client c')
            ->select('u.idUtilisateur, u.nom, u.prenom, u.email')
            ->join('Utilisateur u', 'u.idUtilisateur = c.idUtilisateur')
            ->where('c.idUtilisateur NOT IN (SELECT idUtilisateur FROM Animateur)', null, false)
            ->orderBy('u.nom')->orderBy('u.prenom')
            ->get()->getResultArray();
    }

    /** Fait d'un client un animateur. */
    public function creer(int $idClient, string $specialite, ?int $idRemplacant = null): void
    {
        if ($this->db->table('Client')->where('idUtilisateur', $idClient)->countAllResults() === 0) {
            throw new InvalidArgumentException('Seul un client du club peut devenir animateur.');
        }
        if ($this->find($idClient) !== null) {
            throw new InvalidArgumentException('Ce client est déjà animateur.');
        }

        $this->insert(['idUtilisateur' => $idClient, 'specialite' => trim($specialite)]);
        $this->definirRemplacant($idClient, $idRemplacant);
    }

    public function modifier(int $id, string $specialite, ?int $idRemplacant): void
    {
        $this->definirRemplacant($id, $idRemplacant);
        $this->update($id, ['specialite' => trim($specialite)]);
    }

    /** Réflexivité : le remplaçant est un autre animateur. */
    public function definirRemplacant(int $id, ?int $idRemplacant): void
    {
        if ($idRemplacant !== null) {
            if ($idRemplacant === $id) {
                throw new InvalidArgumentException('Un animateur ne peut pas être son propre remplaçant.');
            }
            if ($this->find($idRemplacant) === null) {
                throw new InvalidArgumentException('Le remplaçant doit être un animateur.');
            }
        }

        $this->db->table('Animateur')->where('idUtilisateur', $id)->update(['idRemplacant' => $idRemplacant]);
    }

    /**
     * Retire le rôle d'animateur : ses événements passent à son remplaçant,
     * les animateurs qu'il remplaçait n'ont plus de remplaçant, ses convocations sont supprimées.
     */
    public function supprimerAnimateur(int $id): void
    {
        $animateur = $this->find($id);
        if ($animateur === null) {
            throw new InvalidArgumentException('Animateur introuvable.');
        }

        $this->db->transStart();
        $this->db->table('Animateur')->where('idRemplacant', $id)->update(['idRemplacant' => null]);
        $this->db->table('Evenement')->where('idAnimateur', $id)->update(['idAnimateur' => $animateur['idRemplacant']]);
        $this->db->table('Convocation')->where('idAnimateur', $id)->delete();
        $this->db->table('Animateur')->where('idUtilisateur', $id)->delete();
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('La suppression de l\'animateur a échoué.');
        }
    }

    /** L'événement passe au remplaçant de son animateur ; renvoie le nouvel animateur ou null. */
    public function remplacerSurEvenement(int $idEvenement): ?int
    {
        $remplacant = $this->db->table('Evenement e')
            ->select('a.idRemplacant')
            ->join('Animateur a', 'a.idUtilisateur = e.idAnimateur')
            ->where('e.idEvenement', $idEvenement)
            ->get()->getRow('idRemplacant');

        if ($remplacant === null) {
            return null;
        }

        $this->db->table('Evenement')->where('idEvenement', $idEvenement)->update(['idAnimateur' => (int) $remplacant]);

        return (int) $remplacant;
    }

    /** Événements qu'il anime (titulaire) ou dont il est le remplaçant de l'animateur. */
    public function evenementsAnimes(int $id): array
    {
        return $this->db->table('Evenement e')
            ->select("e.idEvenement, e.titre, e.type, e.dateDebut, e.dateFin, s.nom AS lieu,
                      CASE WHEN e.idAnimateur = {$id} THEN 'titulaire' ELSE 'remplaçant' END AS role,
                      u.prenom + ' ' + u.nom AS titulaire", false)
            ->join('Animateur a', 'a.idUtilisateur = e.idAnimateur')
            ->join('Utilisateur u', 'u.idUtilisateur = e.idAnimateur')
            ->join('Showroom s', 's.idShowroom = e.idShowroom', 'left')
            ->groupStart()->where('e.idAnimateur', $id)->orWhere('a.idRemplacant', $id)->groupEnd()
            ->orderBy('e.dateDebut')
            ->get()->getResultArray();
    }

    /** Seul l'animateur titulaire ou son remplaçant pointe les présences d'un événement. */
    public function peutPointer(int $idAnimateur, int $idEvenement): bool
    {
        return in_array($idEvenement, array_map('intval', array_column($this->evenementsAnimes($idAnimateur), 'idEvenement')), true);
    }
}
