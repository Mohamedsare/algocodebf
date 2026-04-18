-- ================================================================
-- Migration: Ajout des tables pour plusieurs vidéos et chapitres
-- Date: 2025-01-27
-- Description: Permet d'ajouter plusieurs vidéos par tutoriel et un sommaire avec chapitres
-- ================================================================

USE `hub`;

-- Table: tutorial_videos
-- Stocke plusieurs vidéos pour un tutoriel (formation)
-- ================================================================
CREATE TABLE IF NOT EXISTS `tutorial_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tutorial_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Titre de la vidéo (ex: Chapitre 1, Introduction)',
  `description` text DEFAULT NULL COMMENT 'Description de la vidéo',
  `file_path` varchar(500) NOT NULL COMMENT 'Chemin du fichier vidéo',
  `file_name` varchar(255) NOT NULL COMMENT 'Nom original du fichier',
  `file_size` bigint(20) NOT NULL COMMENT 'Taille du fichier en octets',
  `duration` int(11) DEFAULT NULL COMMENT 'Durée en secondes',
  `order_index` int(11) DEFAULT 0 COMMENT 'Ordre d\'affichage',
  `views` int(11) DEFAULT 0 COMMENT 'Nombre de vues',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tutorial_id` (`tutorial_id`),
  KEY `idx_order_index` (`order_index`),
  CONSTRAINT `tutorial_videos_ibfk_1` FOREIGN KEY (`tutorial_id`) REFERENCES `tutorials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tutorial_chapters
-- Stocke le sommaire/chapitres d'un tutoriel
-- ================================================================
CREATE TABLE IF NOT EXISTS `tutorial_chapters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tutorial_id` int(11) NOT NULL,
  `chapter_number` int(11) NOT NULL COMMENT 'Numéro du chapitre',
  `title` varchar(255) NOT NULL COMMENT 'Titre du chapitre',
  `description` text DEFAULT NULL COMMENT 'Description du chapitre',
  `video_id` int(11) DEFAULT NULL COMMENT 'ID de la vidéo associée (optionnel)',
  `order_index` int(11) DEFAULT 0 COMMENT 'Ordre d\'affichage',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tutorial_id` (`tutorial_id`),
  KEY `idx_video_id` (`video_id`),
  KEY `idx_order_index` (`order_index`),
  CONSTRAINT `tutorial_chapters_ibfk_1` FOREIGN KEY (`tutorial_id`) REFERENCES `tutorials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tutorial_chapters_ibfk_2` FOREIGN KEY (`video_id`) REFERENCES `tutorial_videos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajouter une colonne pour stocker le sommaire JSON dans la table tutorials (optionnel)
-- ================================================================
-- Note: Vérifiez d'abord si la colonne existe avant d'exécuter cette commande
-- Pour MySQL 8.0.19+, vous pouvez utiliser:
-- ALTER TABLE `tutorials` 
-- ADD COLUMN IF NOT EXISTS `table_of_contents` text DEFAULT NULL COMMENT 'Sommaire en JSON (pour compatibilité)';

-- Pour les versions antérieures, vérifiez manuellement et exécutez:
-- ALTER TABLE `tutorials` 
-- ADD COLUMN `table_of_contents` text DEFAULT NULL COMMENT 'Sommaire en JSON (pour compatibilité)';

