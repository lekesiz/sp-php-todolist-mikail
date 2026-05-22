-- =====================================================================
-- SP PHP To Do List — Script SQL v2 (post-retour M. Christoffel 22/05)
-- LP DWCA 2025/2026 — Université de Strasbourg
-- UE 6.1 — Développement Back-End (PHP) — M. Eric Christoffel
-- Mikail Lekesiz & Mickael Hoffer (Groupe 1)
-- Date : 22 mai 2026
-- Encodage : utf8mb4 — SGBD cible : MySQL 8.x / MariaDB 10.x
--
-- Retour du tuteur (22/05/2026) :
--   1. Application mono-utilisateur → suppression de la table `utilisateur`
--   2. Nouvelle table `collaborateur` (id + nom)
--   3. Relation many-to-many `tache` ↔ `collaborateur`
--      avec colonne `pourcentageInvestissement` dans la table d'association
-- =====================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ---------------------------------------------------------------------
-- Base de données : `todoList`
-- ---------------------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `todoList`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `todoList`;

-- =====================================================================
-- 0. Suppression des objets dans l'ordre des dépendances
--    (utile pour une réexécution du script en dev)
-- =====================================================================
DROP TABLE IF EXISTS `tache_collaborateur`;
DROP TABLE IF EXISTS `tache_tag`;
DROP TABLE IF EXISTS `tache`;
DROP TABLE IF EXISTS `tag`;
DROP TABLE IF EXISTS `categorie`;
DROP TABLE IF EXISTS `statut`;
DROP TABLE IF EXISTS `priorite`;
DROP TABLE IF EXISTS `collaborateur`;
-- La table `utilisateur` est retirée du modèle (mono-utilisateur).
DROP TABLE IF EXISTS `utilisateur`;

-- =====================================================================
-- 1. Table : collaborateur  (NOUVELLE — remplace `utilisateur`)
--    L'app est mono-utilisateur ; les collaborateurs servent uniquement
--    à affecter une tâche à une ou plusieurs personnes.
-- =====================================================================
CREATE TABLE `collaborateur` (
  `idCollaborateur`   INT(11)        NOT NULL AUTO_INCREMENT,
  `nomCollaborateur`  VARCHAR(80)    NOT NULL,
  PRIMARY KEY (`idCollaborateur`),
  UNIQUE KEY `uq_nomCollaborateur` (`nomCollaborateur`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- 2. Table : priorite (référence)
-- =====================================================================
CREATE TABLE `priorite` (
  `idPriorite`       TINYINT(4)   NOT NULL AUTO_INCREMENT,
  `libellePriorite`  VARCHAR(30)  NOT NULL,
  `niveauPriorite`   TINYINT(4)   NOT NULL,
  PRIMARY KEY (`idPriorite`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `priorite` (`idPriorite`, `libellePriorite`, `niveauPriorite`) VALUES
  (1, 'Basse',    1),
  (2, 'Normale',  2),
  (3, 'Haute',    3),
  (4, 'Urgente',  4);

-- =====================================================================
-- 3. Table : statut (référence)
-- =====================================================================
CREATE TABLE `statut` (
  `idStatut`        TINYINT(4)   NOT NULL AUTO_INCREMENT,
  `libelleStatut`   VARCHAR(30)  NOT NULL,
  `ordreAffichage`  TINYINT(4)   NOT NULL,
  PRIMARY KEY (`idStatut`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `statut` (`idStatut`, `libelleStatut`, `ordreAffichage`) VALUES
  (1, 'À faire',   1),
  (2, 'En cours',  2),
  (3, 'Bloquée',   3),
  (4, 'Terminée',  4);

-- =====================================================================
-- 4. Table : categorie (référence)
-- =====================================================================
CREATE TABLE `categorie` (
  `idCategorie`        TINYINT(4)   NOT NULL AUTO_INCREMENT,
  `libelleCategorie`   VARCHAR(50)  NOT NULL,
  `couleurCategorie`   CHAR(7)      DEFAULT '#808080',
  PRIMARY KEY (`idCategorie`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `categorie` (`idCategorie`, `libelleCategorie`, `couleurCategorie`) VALUES
  (1, 'Personnel',  '#4CAF50'),
  (2, 'Travail',    '#2196F3'),
  (3, 'Études',     '#9C27B0'),
  (4, 'Maison',     '#FF9800');

-- =====================================================================
-- 5. Table : tag (référence)
-- =====================================================================
CREATE TABLE `tag` (
  `idTag`       INT(11)      NOT NULL AUTO_INCREMENT,
  `libelleTag`  VARCHAR(40)  NOT NULL,
  PRIMARY KEY (`idTag`),
  UNIQUE KEY `uq_libelleTag` (`libelleTag`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- 6. Table : tache (entité centrale)
--    Plus de FK `idUtilisateur` — app mono-utilisateur.
-- =====================================================================
CREATE TABLE `tache` (
  `idTache`            INT(11)        NOT NULL AUTO_INCREMENT,
  `idPriorite`         TINYINT(4)     NOT NULL DEFAULT 2,
  `idStatut`           TINYINT(4)     NOT NULL DEFAULT 1,
  `idCategorie`        TINYINT(4)     DEFAULT NULL,
  `titreTache`         VARCHAR(150)   NOT NULL,
  `descriptionTache`   TEXT,
  `dateCreation`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateEcheance`       DATETIME       DEFAULT NULL,
  `completedAt`        DATETIME       DEFAULT NULL,
  `valideTache`        TINYINT(1)     NOT NULL DEFAULT 1,
  PRIMARY KEY (`idTache`),
  KEY `fk_tache_priorite`    (`idPriorite`),
  KEY `fk_tache_statut`      (`idStatut`),
  KEY `fk_tache_categorie`   (`idCategorie`),
  CONSTRAINT `fk_tache_priorite`  FOREIGN KEY (`idPriorite`)
    REFERENCES `priorite`(`idPriorite`)   ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tache_statut`    FOREIGN KEY (`idStatut`)
    REFERENCES `statut`(`idStatut`)       ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tache_categorie` FOREIGN KEY (`idCategorie`)
    REFERENCES `categorie`(`idCategorie`) ON DELETE SET NULL  ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- 7. Table : tache_collaborateur  (NOUVELLE — N-M avec attribut)
--    Une tâche peut être affectée à plusieurs collaborateurs ;
--    un collaborateur peut être affecté à plusieurs tâches.
--    Colonne supplémentaire : % d'investissement (0–100).
-- =====================================================================
CREATE TABLE `tache_collaborateur` (
  `idTache`                    INT(11)      NOT NULL,
  `idCollaborateur`            INT(11)      NOT NULL,
  `pourcentageInvestissement`  TINYINT(4)   NOT NULL DEFAULT 100,
  `dateAffectation`            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idTache`, `idCollaborateur`),
  KEY `fk_tc_collaborateur` (`idCollaborateur`),
  CONSTRAINT `fk_tc_tache` FOREIGN KEY (`idTache`)
    REFERENCES `tache`(`idTache`)              ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tc_collaborateur` FOREIGN KEY (`idCollaborateur`)
    REFERENCES `collaborateur`(`idCollaborateur`) ON DELETE CASCADE ON UPDATE CASCADE,
  -- Garde-fou : pourcentage entre 0 et 100
  CONSTRAINT `chk_pourcentage` CHECK (`pourcentageInvestissement` BETWEEN 0 AND 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- 8. Table : tache_tag (jonction N-M, inchangée)
-- =====================================================================
CREATE TABLE `tache_tag` (
  `idTache`  INT(11)  NOT NULL,
  `idTag`    INT(11)  NOT NULL,
  PRIMARY KEY (`idTache`, `idTag`),
  KEY `fk_tt_tag` (`idTag`),
  CONSTRAINT `fk_tt_tache` FOREIGN KEY (`idTache`)
    REFERENCES `tache`(`idTache`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tt_tag`   FOREIGN KEY (`idTag`)
    REFERENCES `tag`(`idTag`)     ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- Jeux de tests (exemple — peut être supprimé en production)
-- =====================================================================

INSERT INTO `collaborateur` (`nomCollaborateur`) VALUES
  ('Mikail Lekesiz'),
  ('Mickael Hoffer'),
  ('Eric Christoffel');

INSERT INTO `tag` (`libelleTag`) VALUES
  ('LPDWCA'), ('soutenance'), ('urgent'), ('back-end'), ('react');

INSERT INTO `tache` (`idPriorite`, `idStatut`, `idCategorie`,
                     `titreTache`, `descriptionTache`, `dateEcheance`)
VALUES
  (4, 2, 3, 'Finir le rapport de projet AI Web Content Analyzer',
            'Rédaction des 30 pages et relecture',           '2026-05-21 23:59:00'),
  (3, 1, 3, 'Préparer la soutenance UE 6.5',
            'Slides + démo vidéo',                            '2026-05-28 18:00:00'),
  (2, 1, 2, 'SP PHP To Do List — Étape 2 développement',
            'Implémenter contrôleurs + vues à partir du modèle BDD validé',  '2026-06-15 23:59:00');

-- Affectations many-to-many avec % d'investissement
INSERT INTO `tache_collaborateur` (`idTache`, `idCollaborateur`, `pourcentageInvestissement`) VALUES
  (1, 1, 100),               -- Tâche 1 : Mikail seul (100 %)
  (2, 1, 60), (2, 2, 40),    -- Tâche 2 : Mikail 60 % + Mickael 40 %
  (3, 1, 50), (3, 2, 50);    -- Tâche 3 : 50/50

INSERT INTO `tache_tag` (`idTache`, `idTag`) VALUES
  (1, 1), (1, 2), (1, 3),
  (2, 1), (2, 2),
  (3, 1), (3, 4);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =====================================================================
-- Requêtes de vérification suggérées
-- =====================================================================
-- 1) Tâches avec collaborateurs et % :
-- SELECT t.idTache, t.titreTache, c.nomCollaborateur, tc.pourcentageInvestissement
-- FROM tache t
-- JOIN tache_collaborateur tc ON tc.idTache = t.idTache
-- JOIN collaborateur c        ON c.idCollaborateur = tc.idCollaborateur
-- ORDER BY t.idTache, tc.pourcentageInvestissement DESC;
--
-- 2) Charge par collaborateur :
-- SELECT c.nomCollaborateur, COUNT(tc.idTache) AS nbTaches,
--        SUM(tc.pourcentageInvestissement) AS investTotal
-- FROM collaborateur c
-- LEFT JOIN tache_collaborateur tc ON tc.idCollaborateur = c.idCollaborateur
-- GROUP BY c.idCollaborateur;
-- =====================================================================

-- Fin du script v2.
