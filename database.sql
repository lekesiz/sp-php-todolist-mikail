-- =====================================================================
-- SP PHP To Do List — Export SQL pour déploiement
-- LP DWCA 2025/2026 — Université de Strasbourg
-- UE 6.1 — Développement Back-End (PHP) — M. Eric Christoffel
-- Auteur : Mikail Lekesiz
-- Date    : 22 mai 2026
-- Cible   : MySQL 8.x / MariaDB 10.x — encodage utf8mb4
--
-- IMPORTANT — Différence avec sql/install.sql :
--   Ce fichier ne contient PAS de `CREATE DATABASE` ni de `USE` pour
--   être directement importable dans une base déjà créée (par exemple
--   `lekesiz_todolist` sur AlwaysData, ou n'importe quelle base
--   sélectionnée dans phpMyAdmin).
--
-- Tables : 8
--   1. collaborateur            (référentiel des personnes)
--   2. priorite                 (référence)
--   3. statut                   (référence)
--   4. categorie                (référence)
--   5. tag                      (référence)
--   6. tache                    (entité centrale, mono-utilisateur)
--   7. tache_collaborateur      (N-M avec attribut pourcentageInvestissement)
--   8. tache_tag                (N-M)
-- =====================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- Drop (réexécution propre)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `tache_collaborateur`;
DROP TABLE IF EXISTS `tache_tag`;
DROP TABLE IF EXISTS `tache`;
DROP TABLE IF EXISTS `tag`;
DROP TABLE IF EXISTS `categorie`;
DROP TABLE IF EXISTS `statut`;
DROP TABLE IF EXISTS `priorite`;
DROP TABLE IF EXISTS `collaborateur`;

-- =====================================================================
-- 1. collaborateur — remplace l'ancienne table utilisateur (app mono-user)
-- =====================================================================
CREATE TABLE `collaborateur` (
  `idCollaborateur`   INT(11)        NOT NULL AUTO_INCREMENT,
  `nomCollaborateur`  VARCHAR(80)    NOT NULL,
  PRIMARY KEY (`idCollaborateur`),
  UNIQUE KEY `uq_nomCollaborateur` (`nomCollaborateur`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 2. priorite (référence)
-- =====================================================================
CREATE TABLE `priorite` (
  `idPriorite`       TINYINT(4)   NOT NULL AUTO_INCREMENT,
  `libellePriorite`  VARCHAR(30)  NOT NULL,
  `niveauPriorite`   TINYINT(4)   NOT NULL,
  PRIMARY KEY (`idPriorite`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 3. statut (référence)
-- =====================================================================
CREATE TABLE `statut` (
  `idStatut`        TINYINT(4)   NOT NULL AUTO_INCREMENT,
  `libelleStatut`   VARCHAR(30)  NOT NULL,
  `ordreAffichage`  TINYINT(4)   NOT NULL,
  PRIMARY KEY (`idStatut`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 4. categorie (référence)
-- =====================================================================
CREATE TABLE `categorie` (
  `idCategorie`        TINYINT(4)   NOT NULL AUTO_INCREMENT,
  `libelleCategorie`   VARCHAR(50)  NOT NULL,
  `couleurCategorie`   CHAR(7)      DEFAULT '#808080',
  PRIMARY KEY (`idCategorie`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 5. tag (référence)
-- =====================================================================
CREATE TABLE `tag` (
  `idTag`       INT(11)      NOT NULL AUTO_INCREMENT,
  `libelleTag`  VARCHAR(40)  NOT NULL,
  PRIMARY KEY (`idTag`),
  UNIQUE KEY `uq_libelleTag` (`libelleTag`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 6. tache — entité centrale (plus de FK idUtilisateur, app mono-user)
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 7. tache_collaborateur — N-M AVEC attribut (demande Christoffel 22/05)
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
  CONSTRAINT `chk_pourcentage` CHECK (`pourcentageInvestissement` BETWEEN 0 AND 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 8. tache_tag (jonction N-M classique)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DONNÉES — référentiels
-- =====================================================================
INSERT INTO `priorite` (`idPriorite`, `libellePriorite`, `niveauPriorite`) VALUES
  (1, 'Basse',    1),
  (2, 'Normale',  2),
  (3, 'Haute',    3),
  (4, 'Urgente',  4);

INSERT INTO `statut` (`idStatut`, `libelleStatut`, `ordreAffichage`) VALUES
  (1, 'À faire',   1),
  (2, 'En cours',  2),
  (3, 'Bloquée',   3),
  (4, 'Terminée',  4);

INSERT INTO `categorie` (`idCategorie`, `libelleCategorie`, `couleurCategorie`) VALUES
  (1, 'Personnel',  '#4CAF50'),
  (2, 'Travail',    '#2196F3'),
  (3, 'Études',     '#9C27B0'),
  (4, 'Maison',     '#FF9800');

INSERT INTO `tag` (`idTag`, `libelleTag`) VALUES
  (1, 'LPDWCA'),
  (2, 'soutenance'),
  (3, 'urgent'),
  (4, 'back-end'),
  (5, 'front-end'),
  (6, 'rapport'),
  (7, 'sprint');

-- =====================================================================
-- DONNÉES — collaborateurs (mono-user mais plusieurs collaborateurs)
-- =====================================================================
INSERT INTO `collaborateur` (`idCollaborateur`, `nomCollaborateur`) VALUES
  (1, 'Mikail Lekesiz'),
  (2, 'Mickael Hoffer'),
  (3, 'Eric Christoffel'),
  (4, 'Marie Dupont'),
  (5, 'Thomas Schmitt');

-- =====================================================================
-- DONNÉES — tâches (jeu réaliste, 10 tâches, statuts variés)
-- =====================================================================
INSERT INTO `tache`
  (`idTache`, `idPriorite`, `idStatut`, `idCategorie`,
   `titreTache`, `descriptionTache`, `dateCreation`, `dateEcheance`, `completedAt`)
VALUES
  (1, 4, 4, 3, 'Déposer le rapport Suivi N°6',
              'Rédaction et dépôt du suivi périodique sur DigitalUni',
              '2026-05-21 09:00:00', '2026-05-22 23:59:00', '2026-05-22 12:46:00'),

  (2, 4, 2, 3, 'Application PHP To Do List — Étape 2',
              'Implémenter contrôleurs, modèles et vues à partir du modèle BDD validé par M. Christoffel',
              '2026-05-22 18:00:00', '2026-06-04 23:59:00', NULL),

  (3, 3, 2, 3, 'Rapport AI Web Content Analyzer — sections 1 à 8',
              'Rédiger les 30 pages du rapport projet tuteuré (intro, contexte, architecture, IA, sécurité, tests, déploiement, conclusion)',
              '2026-05-15 10:00:00', '2026-06-15 23:59:00', NULL),

  (4, 3, 1, 3, 'Préparer la soutenance projet tuteuré',
              'Slides Reveal.js (17 slides) + démo screencast 5 minutes + Q/R jury',
              '2026-05-22 22:00:00', '2026-06-25 14:00:00', NULL),

  (5, 2, 1, 2, 'Mettre à jour la convention de stage UE 6.6',
              'Contact ERENEST pour formaliser la convention de stage',
              '2026-05-22 21:00:00', '2026-06-01 17:00:00', NULL),

  (6, 2, 4, 3, 'Patch correctifs AI WCA — PDF wrap, SSRF, fallback title',
              '3 bugfixes : ReportLab/fpdf2 multi_cell, validation URL contre SSRF, fallback title→h1→hostname',
              '2026-05-22 18:30:00', '2026-05-22 19:00:00', '2026-05-22 18:39:00'),

  (7, 3, 1, 3, 'Réviser le cours UE 6.4 — Interactivité Numérique',
              'Préparer l''évaluation finale : React 19, hooks avancés, Server Components',
              '2026-05-23 08:00:00', '2026-06-10 23:59:00', NULL),

  (8, 1, 3, 1, 'Renouveler la carte étudiante 2026/2027',
              'En attente de réception du dossier administratif Université',
              '2026-05-10 14:00:00', '2026-07-15 23:59:00', NULL),

  (9, 2, 1, 4, 'Rendez-vous médecin',
              'Visite annuelle de contrôle, à programmer en juin',
              '2026-05-23 07:30:00', '2026-06-30 18:00:00', NULL),

  (10, 3, 2, 2, 'Déployer l''application To Do List sur AlwaysData',
              'Configurer site, importer SQL, tester, fournir URL au tuteur',
              '2026-05-23 11:00:00', '2026-05-24 23:59:00', NULL);

-- =====================================================================
-- DONNÉES — tache_collaborateur (N-M avec % d'investissement)
-- Total par tâche peut dépasser 100 % (plusieurs personnes à temps plein)
-- ou être inférieur (tâche partagée mais pas tout à temps plein).
-- =====================================================================
INSERT INTO `tache_collaborateur`
  (`idTache`, `idCollaborateur`, `pourcentageInvestissement`, `dateAffectation`)
VALUES
  ( 1, 1, 100, '2026-05-21 09:00:00'),                           -- Suivi N°6 : Mikail seul
  ( 2, 1, 100, '2026-05-22 18:00:00'),                           -- App PHP : Mikail solo (Christoffel a basculé en individuel)
  ( 3, 1, 100, '2026-05-15 10:00:00'),                           -- Rapport AI WCA : Mikail
  ( 4, 1,  70, '2026-05-22 22:00:00'),
  ( 4, 3,  30, '2026-05-22 22:00:00'),                           -- Soutenance : 70 Mikail / 30 Christoffel (tuteur)
  ( 5, 1, 100, '2026-05-22 21:00:00'),
  ( 6, 1, 100, '2026-05-22 18:30:00'),
  ( 7, 1, 100, '2026-05-23 08:00:00'),
  ( 8, 1, 100, '2026-05-10 14:00:00'),
  ( 9, 1, 100, '2026-05-23 07:30:00'),
  (10, 1, 100, '2026-05-23 11:00:00');

-- =====================================================================
-- DONNÉES — tache_tag
-- =====================================================================
INSERT INTO `tache_tag` (`idTache`, `idTag`) VALUES
  ( 1, 1), ( 1, 6),                                              -- Suivi N°6 : LPDWCA + rapport
  ( 2, 1), ( 2, 4), ( 2, 7),                                     -- App PHP : LPDWCA, back-end, sprint
  ( 3, 1), ( 3, 6),                                              -- Rapport AI WCA : LPDWCA, rapport
  ( 4, 1), ( 4, 2), ( 4, 3),                                     -- Soutenance : LPDWCA, soutenance, urgent
  ( 5, 1),
  ( 6, 1), ( 6, 4), ( 6, 5),
  ( 7, 1), ( 7, 5),
  ( 8, 3),
  (10, 1), (10, 4), (10, 7);

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- =====================================================================
-- Vérifications rapides après import
-- =====================================================================
-- SELECT COUNT(*) AS nb_taches FROM tache;                       -- attendu : 10
-- SELECT COUNT(*) AS nb_collab FROM collaborateur;               -- attendu : 5
-- SELECT COUNT(*) AS nb_affect FROM tache_collaborateur;         -- attendu : 11
-- SELECT t.titreTache, c.nomCollaborateur, tc.pourcentageInvestissement
--   FROM tache t
--   JOIN tache_collaborateur tc ON tc.idTache = t.idTache
--   JOIN collaborateur c        ON c.idCollaborateur = tc.idCollaborateur
--   ORDER BY t.idTache, tc.pourcentageInvestissement DESC;
-- =====================================================================
