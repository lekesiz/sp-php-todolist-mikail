<?php
/**
 * Tache — Modèle de l'entité centrale.
 *
 * Gère CRUD + relation many-to-many avec Collaborateur via tache_collaborateur
 * (avec attribut pourcentageInvestissement comme demandé par M. Christoffel).
 */

namespace App\Models;

use App\Database;
use PDO;

final class Tache
{
    public static function all(?string $filterStatut = null): array
    {
        $sql = "
            SELECT t.*,
                   p.libellePriorite, p.niveauPriorite,
                   s.libelleStatut,
                   c.libelleCategorie, c.couleurCategorie
            FROM tache t
            JOIN priorite p ON p.idPriorite = t.idPriorite
            JOIN statut   s ON s.idStatut    = t.idStatut
            LEFT JOIN categorie c ON c.idCategorie = t.idCategorie
            WHERE 1=1
        ";
        $params = [];
        if ($filterStatut !== null && $filterStatut !== '') {
            $sql .= ' AND t.idStatut = :idStatut';
            $params[':idStatut'] = (int) $filterStatut;
        }
        $sql .= ' ORDER BY p.niveauPriorite DESC, t.dateEcheance ASC, t.dateCreation DESC';

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $sql = "
            SELECT t.*,
                   p.libellePriorite, p.niveauPriorite,
                   s.libelleStatut,
                   c.libelleCategorie, c.couleurCategorie
            FROM tache t
            JOIN priorite p ON p.idPriorite = t.idPriorite
            JOIN statut   s ON s.idStatut    = t.idStatut
            LEFT JOIN categorie c ON c.idCategorie = t.idCategorie
            WHERE t.idTache = :id
            LIMIT 1
        ";
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Crée une tâche et retourne son id.
     * @param array<string,mixed> $data
     */
    public static function create(array $data): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO tache
              (idPriorite, idStatut, idCategorie, titreTache, descriptionTache, dateEcheance)
            VALUES
              (:idPriorite, :idStatut, :idCategorie, :titre, :description, :echeance)
        ");
        $stmt->execute([
            ':idPriorite'  => (int) $data['idPriorite'],
            ':idStatut'    => (int) $data['idStatut'],
            ':idCategorie' => $data['idCategorie'] !== '' ? (int) $data['idCategorie'] : null,
            ':titre'       => trim($data['titreTache']),
            ':description' => trim($data['descriptionTache'] ?? '') ?: null,
            ':echeance'    => !empty($data['dateEcheance']) ? $data['dateEcheance'] : null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public static function update(int $id, array $data): bool
    {
        // completedAt logique : si on passe en "Terminée" (idStatut=4), on horodate ;
        // si on en sort, on remet à NULL ; sinon on conserve la valeur actuelle.
        // Calcul côté PHP pour rester portable (MySQL/MariaDB/SQLite tests).
        $current = self::find($id);
        $newStatut = (int) $data['idStatut'];
        $completedAt = $current['completedAt'] ?? null;
        if ($newStatut === 4 && $completedAt === null) {
            $completedAt = date('Y-m-d H:i:s');
        } elseif ($newStatut !== 4) {
            $completedAt = null;
        }

        $stmt = Database::getInstance()->prepare("
            UPDATE tache SET
              idPriorite       = :idPriorite,
              idStatut         = :idStatut,
              idCategorie      = :idCategorie,
              titreTache       = :titre,
              descriptionTache = :description,
              dateEcheance     = :echeance,
              completedAt      = :completedAt
            WHERE idTache = :id
        ");
        return $stmt->execute([
            ':idPriorite'  => (int) $data['idPriorite'],
            ':idStatut'    => $newStatut,
            ':idCategorie' => $data['idCategorie'] !== '' ? (int) $data['idCategorie'] : null,
            ':titre'       => trim($data['titreTache']),
            ':description' => trim($data['descriptionTache'] ?? '') ?: null,
            ':echeance'    => !empty($data['dateEcheance']) ? $data['dateEcheance'] : null,
            ':completedAt' => $completedAt,
            ':id'          => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $stmt = Database::getInstance()->prepare("DELETE FROM tache WHERE idTache = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ---------- Relations many-to-many : collaborateurs ----------

    /** Retourne les collaborateurs affectés à une tâche avec leur % d'investissement. */
    public static function collaborateurs(int $idTache): array
    {
        $stmt = Database::getInstance()->prepare("
            SELECT c.idCollaborateur, c.nomCollaborateur,
                   tc.pourcentageInvestissement, tc.dateAffectation
            FROM tache_collaborateur tc
            JOIN collaborateur c ON c.idCollaborateur = tc.idCollaborateur
            WHERE tc.idTache = :id
            ORDER BY tc.pourcentageInvestissement DESC, c.nomCollaborateur
        ");
        $stmt->execute([':id' => $idTache]);
        return $stmt->fetchAll();
    }

    /**
     * Met à jour la totalité des affectations d'une tâche en une transaction.
     *
     * @param int                                   $idTache
     * @param array<int,int>                        $assignments  [idCollaborateur => pourcentage]
     */
    public static function syncCollaborateurs(int $idTache, array $assignments): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $pdo->prepare("DELETE FROM tache_collaborateur WHERE idTache = :id")
                ->execute([':id' => $idTache]);

            if (!empty($assignments)) {
                $ins = $pdo->prepare("
                    INSERT INTO tache_collaborateur (idTache, idCollaborateur, pourcentageInvestissement)
                    VALUES (:idTache, :idCollaborateur, :pourcent)
                ");
                foreach ($assignments as $idCollab => $pourcent) {
                    $pourcent = max(0, min(100, (int) $pourcent));
                    $ins->execute([
                        ':idTache'         => $idTache,
                        ':idCollaborateur' => (int) $idCollab,
                        ':pourcent'        => $pourcent,
                    ]);
                }
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // ---------- Helpers statistiques ----------

    /** @return array<string,int> */
    public static function countsByStatut(): array
    {
        $rows = Database::getInstance()->query("
            SELECT s.libelleStatut AS lib, COUNT(t.idTache) AS n
            FROM statut s
            LEFT JOIN tache t ON t.idStatut = s.idStatut
            GROUP BY s.idStatut
            ORDER BY s.ordreAffichage
        ")->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['lib']] = (int) $r['n'];
        }
        return $out;
    }
}
