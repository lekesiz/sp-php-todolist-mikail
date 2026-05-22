<?php
/**
 * Collaborateur — Modèle (id, nom) demandé par M. Christoffel.
 */

namespace App\Models;

use App\Database;

final class Collaborateur
{
    public static function all(): array
    {
        return Database::getInstance()
            ->query("SELECT * FROM collaborateur ORDER BY nomCollaborateur")
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare(
            "SELECT * FROM collaborateur WHERE idCollaborateur = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $nom): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "INSERT INTO collaborateur (nomCollaborateur) VALUES (:nom)"
        );
        $stmt->execute([':nom' => trim($nom)]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, string $nom): bool
    {
        $stmt = Database::getInstance()->prepare(
            "UPDATE collaborateur SET nomCollaborateur = :nom WHERE idCollaborateur = :id"
        );
        return $stmt->execute([':nom' => trim($nom), ':id' => $id]);
    }

    public static function delete(int $id): bool
    {
        // ON DELETE CASCADE prend en charge tache_collaborateur
        $stmt = Database::getInstance()->prepare(
            "DELETE FROM collaborateur WHERE idCollaborateur = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    /** Charge de travail : nb de tâches + somme des % d'investissement. */
    public static function workload(): array
    {
        return Database::getInstance()->query("
            SELECT c.idCollaborateur, c.nomCollaborateur,
                   COUNT(tc.idTache)                       AS nbTaches,
                   COALESCE(SUM(tc.pourcentageInvestissement), 0) AS investTotal
            FROM collaborateur c
            LEFT JOIN tache_collaborateur tc ON tc.idCollaborateur = c.idCollaborateur
            GROUP BY c.idCollaborateur
            ORDER BY c.nomCollaborateur
        ")->fetchAll();
    }
}
