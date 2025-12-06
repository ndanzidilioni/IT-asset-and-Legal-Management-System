-- Migration to create court_proceedings table for tracking all court hearing activities
-- Run this migration after legal_cases table exists

USE `scheduling_management_system`;

CREATE TABLE IF NOT EXISTS `court_proceedings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id` BIGINT UNSIGNED NOT NULL,
  `hearing_date` DATE NOT NULL,
  `name_of_court` VARCHAR(255) NULL,
  `parties` VARCHAR(500) NULL,
  `case_number` VARCHAR(100) NULL,
  `court_judge` VARCHAR(255) NULL,
  `clerk_karani` VARCHAR(255) NULL,
  `advocate_for_opponent` VARCHAR(255) NULL,
  `advocate_for_moi` VARCHAR(255) NULL,
  `proceedings` TEXT NULL,
  `court_order` TEXT NULL,
  `next_date` DATE NULL,
  `remarks` TEXT NULL,
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_case_id` (`case_id`),
  INDEX `idx_hearing_date` (`hearing_date`),
  INDEX `idx_next_date` (`next_date`),
  CONSTRAINT `fk_proceedings_case` FOREIGN KEY (`case_id`) REFERENCES `legal_cases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_proceedings_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
