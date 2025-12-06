-- Migration to add case_year, parties, amount_in_claim, and any_appeal columns to legal_cases table
-- Run this migration after the initial legal_system_consolidated.sql

USE `scheduling_management_system`;

-- Add new columns to legal_cases table
ALTER TABLE `legal_cases`
  ADD COLUMN `case_year` VARCHAR(20) NULL AFTER `case_number`,
  ADD COLUMN `parties` VARCHAR(500) NULL AFTER `case_type`,
  ADD COLUMN `amount_in_claim` DECIMAL(15, 2) NULL AFTER `estimated_value`,
  ADD COLUMN `any_appeal` VARCHAR(100) NULL DEFAULT 'No' AFTER `outcome`;

-- Update existing records to have default values
UPDATE `legal_cases` SET `any_appeal` = 'No' WHERE `any_appeal` IS NULL;

-- Add indexes for better query performance
CREATE INDEX `idx_case_year` ON `legal_cases` (`case_year`);
CREATE INDEX `idx_any_appeal` ON `legal_cases` (`any_appeal`);
