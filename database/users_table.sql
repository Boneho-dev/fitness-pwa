-- ============================================
-- AGRE FITNESS - TABLE UTILISATEURS
-- ============================================

CREATE TABLE IF NOT EXISTS `fitness_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `gender` ENUM('male', 'female', 'Homme', 'Femme') NOT NULL,
  `age` INT NULL,
  `height` INT NULL COMMENT 'Taille en cm',
  `current_weight` DECIMAL(5,2) NULL COMMENT 'Poids actuel en kg',
  `goal_weight` DECIMAL(5,2) NULL COMMENT 'Poids objectif en kg',
  `bio` TEXT NULL,
  `profile_pic` VARCHAR(255) NULL DEFAULT 'default_avatar.png',
  `last_active` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_username (`username`),
  INDEX idx_email (`email`),
  INDEX idx_created_at (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
