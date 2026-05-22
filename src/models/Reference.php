<?php
/**
 * Reference — Helpers pour les tables de référence
 * (priorite, statut, categorie, tag).
 * Toutes ces tables sont en lecture seule depuis l'UI.
 */

namespace App\Models;

use App\Database;

final class Reference
{
    public static function priorites(): array
    {
        return Database::getInstance()
            ->query("SELECT * FROM priorite ORDER BY niveauPriorite")
            ->fetchAll();
    }

    public static function statuts(): array
    {
        return Database::getInstance()
            ->query("SELECT * FROM statut ORDER BY ordreAffichage")
            ->fetchAll();
    }

    public static function categories(): array
    {
        return Database::getInstance()
            ->query("SELECT * FROM categorie ORDER BY libelleCategorie")
            ->fetchAll();
    }

    public static function tags(): array
    {
        return Database::getInstance()
            ->query("SELECT * FROM tag ORDER BY libelleTag")
            ->fetchAll();
    }
}
