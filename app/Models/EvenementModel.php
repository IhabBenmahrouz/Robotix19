<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class EvenementModel extends Model
{
    public const TYPES = [
        'demo'      => 'Démonstration',
        'lancement' => 'Lancement',
        'salon'     => 'Salon',
        'atelier'   => 'Atelier',
    ];

    protected $table         = 'Evenement';
    protected $primaryKey    = 'idEvenement';
    protected $returnType    = 'array';
    protected $allowedFields = ['titre', 'description', 'type', 'dateDebut', 'dateFin', 'idShowroom', 'idProduit', 'idAnimateur'];

    private function avecDetails(): BaseBuilder
    {
        return $this->db->table('Evenement e')
            ->select("e.idEvenement, e.titre, e.description, e.type, e.dateDebut, e.dateFin, e.idShowroom, e.idProduit, e.idAnimateur,
                      s.nom AS showroom, s.ville, p.nom AS produit, ua.prenom + ' ' + ua.nom AS animateur", false)
            ->join('Showroom s', 's.idShowroom = e.idShowroom', 'left')
            ->join('Produit p', 'p.idProduit = e.idProduit', 'left')
            ->join('Utilisateur ua', 'ua.idUtilisateur = e.idAnimateur', 'left');
    }

    public function prochains(int $n = 3): array
    {
        return $this->avecDetails()
            ->where('e.dateFin >= GETDATE()', null, false)
            ->orderBy('e.dateDebut', 'ASC')->limit($n)
            ->get()->getResultArray();
    }

    /** Événements qui touchent le mois « AAAA-MM » (y compris ceux à cheval sur deux mois). */
    public function duMois(string $mois): array
    {
        $debut = $mois . '-01T00:00:00';
        $fin   = date('Y-m-d\TH:i:s', strtotime($mois . '-01 +1 month'));

        return $this->avecDetails()
            ->where('e.dateDebut <', $fin)
            ->where('e.dateFin >=', $debut)
            ->orderBy('e.dateDebut', 'ASC')
            ->get()->getResultArray();
    }

    /** AP3 : événements à venir avec type, lieu, animateur et places restantes (inscriptions et réservations déduites). */
    public function aVenirDetailles(?string $type = null): array
    {
        $requete = $this->db->table('Evenement e')
            ->select("e.idEvenement, e.titre, e.type, t.libelle AS typeLibelle, e.dateDebut, e.dateFin, e.nbPlaces,
                      s.nom AS lieu, u.prenom + ' ' + u.nom AS animateur,
                      e.nbPlaces - (SELECT COUNT(*) FROM Inscription i WHERE i.idEvenement = e.idEvenement)
                                 - ISNULL((SELECT SUM(p.nbPlace) FROM Panier p WHERE p.idEvenement = e.idEvenement), 0) AS placesRestantes", false)
            ->join('TypeEvenement t', 't.code = e.type')
            ->join('Showroom s', 's.idShowroom = e.idShowroom', 'left')
            ->join('Utilisateur u', 'u.idUtilisateur = e.idAnimateur', 'left')
            ->where('e.dateDebut > GETDATE()', null, false);

        if ($type !== null) {
            $requete->where('e.type', $type);
        }

        return $requete->orderBy('e.dateDebut')->get()->getResultArray();
    }

    public function tous(): array
    {
        return $this->avecDetails()->orderBy('e.dateDebut', 'DESC')->get()->getResultArray();
    }

    /** Vrai si le showroom a déjà un événement qui recoupe [$debut ; $fin[. */
    public function chevauche(?int $idShowroom, string $debut, string $fin, ?int $exclure = null): bool
    {
        if ($idShowroom === null) {
            return false;
        }

        $requete = $this->db->table('Evenement')
            ->where('idShowroom', $idShowroom)
            ->where('dateDebut <', $fin)
            ->where('dateFin >', $debut);

        if ($exclure !== null) {
            $requete->where('idEvenement !=', $exclure);
        }

        return $requete->countAllResults() > 0;
    }
}
