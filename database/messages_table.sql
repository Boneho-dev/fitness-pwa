-- ============================================
-- MESSAGERIE V1.1 - AGRE FITNESS
-- Table des messages privés entre utilisateurs
-- ============================================

CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `expediteur_id` INT NOT NULL,
  `destinataire_id` INT NOT NULL,
  `contenu` TEXT,
  `media_url` VARCHAR(255) DEFAULT NULL,
  `lu` TINYINT(1) DEFAULT 0,
  `date_envoi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`expediteur_id`) REFERENCES `fitness_users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`destinataire_id`) REFERENCES `fitness_users`(`id`) ON DELETE CASCADE,
  INDEX idx_expediteur (`expediteur_id`),
  INDEX idx_destinataire (`destinataire_id`),
  INDEX idx_date (`date_envoi`),
  INDEX idx_lu (`lu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vue pour obtenir les conversations récentes par utilisateur
-- Affiche le dernier message de chaque conversation
-- ============================================

-- Note : Cette requête sera utilisée dans l'application pour afficher
-- la liste des conversations dans messages.php
