<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class ProduitModel extends Model
{
    protected $table         = 'Produit';
    protected $primaryKey    = 'idProduit';
    protected $returnType    = 'array';
    protected $allowedFields = ['reference', 'nom', 'description', 'prixHt', 'stock', 'actif', 'tauxTva', 'idCategorie', 'idMarque'];

    /** Robots actifs avec marque, catégorie et deux premières images. */
    private function base(): BaseBuilder
    {
        return $this->db->table('Produit p')
            ->select('p.idProduit, p.reference, p.nom, p.description, p.prixHt, p.tauxTva, p.stock, p.dateAjout,
                      p.idCategorie, p.idMarque, p.statutCommercial, m.nom AS marque, m.siege AS marqueSiege,
                      m.siteWeb AS marqueSite, m.pays AS marquePays, c.libelle AS categorie,
                      i1.fichier AS image, i2.fichier AS image2')
            ->join('Marque m', 'm.idMarque = p.idMarque')
            ->join('Categorie c', 'c.idCategorie = p.idCategorie')
            ->join('Image i1', 'i1.idProduit = p.idProduit AND i1.numOrdre = 1', 'left')
            ->join('Image i2', 'i2.idProduit = p.idProduit AND i2.numOrdre = 2', 'left')
            ->where('p.actif', 1);
    }

    public function phares(int $n = 4): array
    {
        return $this->base()->orderBy('p.prixHt', 'DESC')->limit($n)->get()->getResultArray();
    }

    /**
     * @param array{categorie?: int|string, marque?: int|string, prixMax?: int|string, q?: string, tri?: string} $filtres
     */
    public function catalogue(array $filtres): array
    {
        $requete = $this->base();

        if (! empty($filtres['categorie'])) {
            $requete->where('p.idCategorie', (int) $filtres['categorie']);
        }
        if (! empty($filtres['marque'])) {
            $requete->where('p.idMarque', (int) $filtres['marque']);
        }
        if (! empty($filtres['prixMax'])) {
            // Filtre sur le prix TTC ; la valeur est convertie en nombre, donc sans risque d'injection
            $requete->where('p.prixHt * (1 + p.tauxTva / 100) <= ' . (float) $filtres['prixMax'], null, false);
        }
        if (! empty($filtres['q'])) {
            $requete->like('p.nom', trim((string) $filtres['q']));
        }

        match ($filtres['tri'] ?? 'nouveautes') {
            'prix_asc'  => $requete->orderBy('p.prixHt', 'ASC'),
            'prix_desc' => $requete->orderBy('p.prixHt', 'DESC'),
            default     => $requete->orderBy('p.dateAjout', 'DESC')->orderBy('p.nom', 'ASC'),
        };

        return $requete->get()->getResultArray();
    }

    public function fiche(int $id): ?array
    {
        return $this->base()->where('p.idProduit', $id)->get()->getRowArray();
    }

    /** Robots déclarés compatibles (dans un sens ou dans l'autre). */
    public function compatibles(int $id): array
    {
        $paires = $this->db->table('CompatibiliteProduit')
            ->groupStart()->where('idProduit1', $id)->orWhere('idProduit2', $id)->groupEnd()
            ->get()->getResultArray();

        $autres = array_map(
            static fn (array $paire): int => (int) $paire['idProduit1'] === $id ? (int) $paire['idProduit2'] : (int) $paire['idProduit1'],
            $paires,
        );

        return $autres === [] ? [] : $this->base()->whereIn('p.idProduit', $autres)->get()->getResultArray();
    }

    /** Une ligne par catégorie : prix HT minimum et nombre de robots actifs. */
    public function gammes(): array
    {
        return $this->db->query(
            'SELECT c.idCategorie, c.libelle, c.description, g.prixMin, g.nbRobots
             FROM Categorie c
             JOIN (SELECT idCategorie, MIN(prixHt) AS prixMin, COUNT(*) AS nbRobots
                   FROM Produit WHERE actif = 1 GROUP BY idCategorie) g
               ON g.idCategorie = c.idCategorie
             ORDER BY g.prixMin'
        )->getResultArray();
    }

    /** Liste légère (id, nom, prix) pour le calculateur et les listes déroulantes. */
    public function pourCalculateur(): array
    {
        return $this->select('idProduit, nom, prixHt, tauxTva')
            ->where('actif', 1)->orderBy('nom')->findAll();
    }
}
