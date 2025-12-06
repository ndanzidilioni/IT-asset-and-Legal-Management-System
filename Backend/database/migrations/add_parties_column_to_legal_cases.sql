-- Add missing columns to legal_cases table
-- These columns are required by the frontend form

USE `scheduling`;

-- Add case_year column (required field from frontend)
ALTER TABLE `legal_cases` 
ADD COLUMN IF NOT EXISTS `case_year` VARCHAR(20) NULL AFTER `case_number`;

-- Add parties column (required field from frontend)
ALTER TABLE `legal_cases` 
ADD COLUMN IF NOT EXISTS `parties` VARCHAR(500) NULL AFTER `case_title`;

-- Ensure amount_in_claim exists
ALTER TABLE `legal_cases` 
ADD COLUMN IF NOT EXISTS `amount_in_claim` DECIMAL(15,2) NULL;

-- Make case_title nullable since it's been removed from frontend
ALTER TABLE `legal_cases` 
MODIFY COLUMN `case_title` VARCHAR(255) NULL;

-- Add any_appeal column
ALTER TABLE `legal_cases` 
ADD COLUMN IF NOT EXISTS `any_appeal` VARCHAR(100) NULL DEFAULT 'No';
