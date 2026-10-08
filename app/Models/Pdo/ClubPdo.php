<?php

namespace App\Models\Pdo;

use App\Libraries\RobotixPdo;
use DomainException;
use InvalidArgumentException;

/**
 * Accès PDO aux données du Club Robotix : adhérents, catégories d'âge, formules et adhésions.
 */
class ClubPdo
{
    protected RobotixPdo $pdo;

    public function __construct(?RobotixPdo $pdo = null)
    {
        $this->pdo = $pdo ?? RobotixPdo::instance();
        helper('robotix');
    }

    public function categoriesAge(): array
    {
        $lignes = $this->pdo->lignes(
            'SELECT ca.idCategorieAge, ca.libelle, ca.ageMin, ca.ageMax, r.txReduction
             FROM CategorieAge ca
             JOIN Reduction r ON r.idCategorieAge = ca.idCategorieAge
             ORDER BY ca.ageMin',
        );

        return array_map(static fn (array $c): array => ['txReduction' => (float) $c['txReduction']] + $c, $lignes);
    }

    public function categoriePourAge(int $age): ?array
    {
        return $this->pdo->ligne(
            'SELECT ca.idCategorieAge, ca.libelle, r.txReduction
             FROM CategorieAge ca
             JOIN Reduction r ON r.idCategorieAge = ca.idCategorieAge
             WHERE :age BETWEEN ca.ageMin AND ca.ageMax',
            ['age' => $age],
        );
    }

    public function formules(): array
    {
        $lignes = $this->pdo->lignes(
            "SELECT idTarif, code, libelle, valeur, description
             FROM Tarif WHERE famille = 'formule' AND actif = 1 ORDER BY valeur",
        );

        return array_map(static fn (array $t): array => ['valeur' => (float) $t['valeur']] + $t, $lignes);
    }

    /** Catégories de robots, proposées comme centres d'intérêt. */
    public function categoriesProduit(): array
    {
        return $this->pdo->lignes('SELECT idCategorie, libelle FROM Categorie ORDER BY libelle');
    }

    /** Prix de la formule après la réduction de la catégorie d'âge. */
    public function montantAdhesion(int $idTarif, int $idCategorieAge): float
    {
        $montant = $this->pdo->valeur(
            "SELECT CAST(ROUND(t.valeur * (100 - r.txReduction) / 100, 2) AS DECIMAL(10,2))
             FROM Tarif t CROSS JOIN Reduction r
             WHERE t.idTarif = :tarif AND t.famille = 'formule' AND r.idCategorieAge = :categorie",
            ['tarif' => $idTarif, 'categorie' => $idCategorieAge],
        );

        if ($montant === null) {
            throw new InvalidArgumentException('Formule ou catégorie inconnue.');
        }

        return (float) $montant;
    }

    /**
     * Enregistre un nouvel adhérent : compte, client, adresse, centres d'intérêt
     * et adhésion de l'année, dans une seule transaction.
     */
    public function inscrire(array $d): int
    {
        $categorie = $this->categoriePourAge(age_le($d['dateNaissance']));

        if ($categorie === null) {
            throw new DomainException('Il faut avoir au moins 18 ans pour adhérer au Club Robotix.');
        }

        $montant = $this->montantAdhesion((int) $d['idTarif'], (int) $categorie['idCategorieAge']);

        return $this->pdo->transaction(function (RobotixPdo $pdo) use ($d, $categorie, $montant): int {
            $id = (int) $pdo->valeur(
                'INSERT INTO Utilisateur (nom, prenom, email, motDePasse, actif)
                 OUTPUT INSERTED.idUtilisateur
                 VALUES (:nom, :prenom, :email, :motDePasse, 1)',
                [
                    'nom'        => trim($d['nom']),
                    'prenom'     => trim($d['prenom']),
                    'email'      => strtolower(trim($d['email'])),
                    'motDePasse' => password_hash($d['motDePasse'], PASSWORD_DEFAULT),
                ],
            );

            $pdo->executer(
                'INSERT INTO Client (idUtilisateur, telephone, dateNaissance, photo, idCategorieAge)
                 VALUES (:id, :telephone, :naissance, :photo, :categorie)',
                [
                    'id' => $id, 'telephone' => $d['telephone'], 'naissance' => $d['dateNaissance'],
                    'photo' => $d['photo'] ?? null, 'categorie' => (int) $categorie['idCategorieAge'],
                ],
            );

            $pdo->executer(
                "INSERT INTO Adresse (libelle, ligne1, codePostal, ville, pays, idUtilisateur)
                 VALUES ('Domicile', :ligne1, :cp, :ville, 'France', :id)",
                ['ligne1' => trim($d['adresse']), 'cp' => $d['codePostal'], 'ville' => trim($d['ville']), 'id' => $id],
            );

            foreach (array_unique(array_map('intval', $d['interets'] ?? [])) as $idCategorie) {
                $pdo->executer(
                    'INSERT INTO ClientInteret (idUtilisateur, idCategorie) VALUES (:id, :categorie)',
                    ['id' => $id, 'categorie' => $idCategorie],
                );
            }

            $pdo->executer(
                'INSERT INTO Adhesion (idUtilisateur, annee, idTarif, montant)
                 VALUES (:id, YEAR(GETDATE()), :tarif, :montant)',
                ['id' => $id, 'tarif' => (int) $d['idTarif'], 'montant' => number_format($montant, 2, '.', '')],
            );

            return $id;
        });
    }

    /** Fiche d'un adhérent avec sa dernière adhésion, ou null si ce n'est pas un client. */
    public function profil(int $id): ?array
    {
        return $this->pdo->ligne(
            'SELECT u.idUtilisateur, u.nom, u.prenom, u.email, u.actif, u.dateInscription,
                    c.telephone, c.photo, c.dateNaissance, ca.libelle AS categorie, r.txReduction,
                    a.annee, a.dateAdhesion, a.montant, a.idTarif, t.libelle AS formule
             FROM Client c
             JOIN Utilisateur u ON u.idUtilisateur = c.idUtilisateur
             LEFT JOIN CategorieAge ca ON ca.idCategorieAge = c.idCategorieAge
             LEFT JOIN Reduction r ON r.idCategorieAge = c.idCategorieAge
             OUTER APPLY (SELECT TOP 1 annee, dateAdhesion, montant, idTarif
                          FROM Adhesion WHERE idUtilisateur = c.idUtilisateur ORDER BY annee DESC) a
             LEFT JOIN Tarif t ON t.idTarif = a.idTarif
             WHERE c.idUtilisateur = :id',
            ['id' => $id],
        );
    }

    /** @return list<string> libellés des catégories de robots suivies */
    public function interetsDe(int $id): array
    {
        return array_column($this->pdo->lignes(
            'SELECT cat.libelle FROM ClientInteret ci
             JOIN Categorie cat ON cat.idCategorie = ci.idCategorie
             WHERE ci.idUtilisateur = :id ORDER BY cat.libelle',
            ['id' => $id],
        ), 'libelle');
    }

    public function reservationsDe(int $id): array
    {
        return $this->pdo->lignes(
            'SELECT p.nomEvenement, e.dateDebut, p.nbPlace, p.dateResa
             FROM Panier p JOIN Evenement e ON e.idEvenement = p.idEvenement
             WHERE p.idUtilisateur = :id ORDER BY e.dateDebut',
            ['id' => $id],
        );
    }

    /** Change (ou crée) l'adhésion de l'année en cours avec une autre formule. */
    public function changerFormule(int $id, int $idTarif): array
    {
        $client = $this->pdo->ligne('SELECT idCategorieAge FROM Client WHERE idUtilisateur = :id', ['id' => $id]);
        $formule = $this->pdo->ligne("SELECT libelle FROM Tarif WHERE idTarif = :id AND famille = 'formule' AND actif = 1", ['id' => $idTarif]);

        if ($client === null || $client['idCategorieAge'] === null || $formule === null) {
            throw new InvalidArgumentException('Formule indisponible pour ce compte.');
        }

        $montant = $this->montantAdhesion($idTarif, (int) $client['idCategorieAge']);
        $valeurs = ['tarif' => $idTarif, 'montant' => number_format($montant, 2, '.', ''), 'id' => $id];

        $this->pdo->transaction(function (RobotixPdo $pdo) use ($valeurs): void {
            $modifiees = $pdo->executer(
                'UPDATE Adhesion SET idTarif = :tarif, montant = :montant WHERE idUtilisateur = :id AND annee = YEAR(GETDATE())',
                $valeurs,
            );
            if ($modifiees === 0) {
                $pdo->executer(
                    'INSERT INTO Adhesion (idTarif, montant, idUtilisateur, annee) VALUES (:tarif, :montant, :id, YEAR(GETDATE()))',
                    $valeurs,
                );
            }
        });

        return ['formule' => $formule['libelle'], 'montant' => $montant];
    }

    /** Adhérents (tous, ou d'une catégorie d'âge) avec leur dernière adhésion. */
    public function adherents(?int $idCategorieAge = null): array
    {
        $sql = 'SELECT u.idUtilisateur, u.nom, u.prenom, u.email, u.actif, u.dateInscription,
                       c.telephone, c.photo, c.dateNaissance, ca.libelle AS categorie,
                       a.annee, a.montant, t.libelle AS formule
                FROM Client c
                JOIN Utilisateur u ON u.idUtilisateur = c.idUtilisateur
                LEFT JOIN CategorieAge ca ON ca.idCategorieAge = c.idCategorieAge
                OUTER APPLY (SELECT TOP 1 annee, montant, idTarif
                             FROM Adhesion WHERE idUtilisateur = c.idUtilisateur ORDER BY annee DESC) a
                LEFT JOIN Tarif t ON t.idTarif = a.idTarif';
        $parametres = [];

        if ($idCategorieAge !== null) {
            $sql .= ' WHERE c.idCategorieAge = :categorie';
            $parametres['categorie'] = $idCategorieAge;
        }

        return $this->pdo->lignes($sql . ' ORDER BY u.nom, u.prenom', $parametres);
    }

    /** Mise à jour d'un adhérent par l'administrateur ; la catégorie suit la date de naissance. */
    public function modifier(int $id, array $d): void
    {
        $email = strtolower(trim($d['email']));
        $pris  = (int) $this->pdo->valeur(
            'SELECT COUNT(*) FROM Utilisateur WHERE email = :email AND idUtilisateur <> :id',
            ['email' => $email, 'id' => $id],
        );
        if ($pris > 0) {
            throw new DomainException('Cet e-mail est déjà utilisé par un autre compte.');
        }

        $categorie = $this->categoriePourAge(age_le($d['dateNaissance']));
        if ($categorie === null) {
            throw new DomainException('La date de naissance doit correspondre à un adhérent majeur.');
        }

        $this->pdo->transaction(function (RobotixPdo $pdo) use ($id, $d, $email, $categorie): void {
            $pdo->executer(
                'UPDATE Utilisateur SET nom = :nom, prenom = :prenom, email = :email, actif = :actif WHERE idUtilisateur = :id',
                ['nom' => trim($d['nom']), 'prenom' => trim($d['prenom']), 'email' => $email, 'actif' => ! empty($d['actif']) ? 1 : 0, 'id' => $id],
            );
            $pdo->executer(
                'UPDATE Client SET telephone = :telephone, dateNaissance = :naissance, idCategorieAge = :categorie WHERE idUtilisateur = :id',
                ['telephone' => $d['telephone'], 'naissance' => $d['dateNaissance'], 'categorie' => (int) $categorie['idCategorieAge'], 'id' => $id],
            );
        });
    }

    /** Supprime un adhérent et ses données liées ; refusé (false) s'il a des commandes. */
    public function supprimer(int $id): bool
    {
        if ((int) $this->pdo->valeur('SELECT COUNT(*) FROM Commande WHERE idUtilisateur = :id', ['id' => $id]) > 0) {
            return false;
        }

        $this->pdo->transaction(function (RobotixPdo $pdo) use ($id): void {
            foreach (['ClientInteret', 'Adhesion', 'Panier', 'Adresse', 'Suivre', 'Client', 'Utilisateur'] as $table) {
                $pdo->executer("DELETE FROM {$table} WHERE idUtilisateur = :id", ['id' => $id]);
            }
        });

        return true;
    }
}
