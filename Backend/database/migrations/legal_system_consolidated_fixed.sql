-- ============================================
-- LEGAL MANAGEMENT SYSTEM - CONSOLIDATED DATABASE (FIXED)
-- Resolves foreign key constraint issues
-- ============================================

USE `scheduling_management_system`;

-- First, check users table structure
-- If users.id is not BIGINT UNSIGNED, we need to handle this

-- ============================================
-- 1. CLIENT MANAGEMENT
-- ============================================

CREATE TABLE IF NOT EXISTS `legal_clients` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_number` VARCHAR(50) NOT NULL UNIQUE,
  `client_type` ENUM('Individual', 'Corporate', 'Government', 'NGO', 'Other') NOT NULL DEFAULT 'Individual',
  `full_name` VARCHAR(255) NOT NULL,
  `short_name` VARCHAR(100) NULL,
  `email` VARCHAR(255) NULL,
  `phone` VARCHAR(50) NULL,
  `mobile` VARCHAR(50) NULL,
  `address` TEXT NULL,
  `city` VARCHAR(100) NULL,
  `region` VARCHAR(100) NULL,
  `country` VARCHAR(100) DEFAULT 'Tanzania',
  `company_registration_number` VARCHAR(100) NULL,
  `tax_identification_number` VARCHAR(100) NULL,
  `industry` VARCHAR(100) NULL,
  `national_id` VARCHAR(50) NULL,
  `passport_number` VARCHAR(50) NULL,
  `date_of_birth` DATE NULL,
  `gender` ENUM('Male', 'Female', 'Other', 'Prefer not to say') NULL,
  `billing_address` TEXT NULL,
  `payment_terms` VARCHAR(100) NULL DEFAULT 'Net 30',
  `status` ENUM('Active', 'Inactive', 'Prospective', 'Blacklisted') NOT NULL DEFAULT 'Active',
  `notes` TEXT NULL,
  `assigned_lawyer_id` BIGINT UNSIGNED NULL,
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_client_number` (`client_number`),
  INDEX `idx_full_name` (`full_name`),
  INDEX `idx_status` (`status`),
  INDEX `idx_assigned_lawyer_id` (`assigned_lawyer_id`),
  INDEX `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. CASE MANAGEMENT
-- ============================================

CREATE TABLE IF NOT EXISTS `legal_cases` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_number` VARCHAR(100) NOT NULL UNIQUE,
  `case_title` VARCHAR(255) NOT NULL,
  `case_type` ENUM('Civil', 'Criminal', 'Corporate', 'Labor', 'Family', 'Constitutional', 'Administrative', 'Other') NOT NULL DEFAULT 'Civil',
  `client_id` BIGINT UNSIGNED NULL,
  `client_name` VARCHAR(255) NULL,
  `court_name` VARCHAR(255) NULL,
  `judge_name` VARCHAR(255) NULL,
  `opposing_party` VARCHAR(255) NULL,
  `case_description` TEXT NULL,
  `filing_date` DATE NULL,
  `hearing_date` DATE NULL,
  `expected_completion_date` DATE NULL,
  `closed_date` DATE NULL,
  `status` ENUM('Active', 'Pending', 'Completed', 'On Hold', 'Closed', 'Dismissed') NOT NULL DEFAULT 'Pending',
  `priority` ENUM('Low', 'Medium', 'High', 'Urgent') NOT NULL DEFAULT 'Medium',
  `estimated_value` DECIMAL(15, 2) NULL,
  `legal_fees` DECIMAL(15, 2) NULL DEFAULT 0,
  `documents_path` VARCHAR(500) NULL,
  `notes` TEXT NULL,
  `outcome` TEXT NULL,
  `assigned_lawyer_id` BIGINT UNSIGNED NULL,
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_case_number` (`case_number`),
  INDEX `idx_status` (`status`),
  INDEX `idx_client_id` (`client_id`),
  INDEX `idx_assigned_lawyer_id` (`assigned_lawyer_id`),
  INDEX `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. CONTRACT REGISTER
-- ============================================

CREATE TABLE IF NOT EXISTS `legal_contracts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_number` VARCHAR(100) NOT NULL UNIQUE,
  `contract_title` VARCHAR(255) NOT NULL,
  `contract_type` ENUM('Tender', 'Service Agreement', 'Employment', 'Lease', 'Partnership', 'NDA', 'MOU', 'Other') NOT NULL DEFAULT 'Service Agreement',
  `description` TEXT NULL,
  `party_a` VARCHAR(255) NOT NULL,
  `party_b` VARCHAR(255) NOT NULL,
  `client_id` BIGINT UNSIGNED NULL,
  `contract_value` DECIMAL(15, 2) NULL,
  `currency` VARCHAR(10) DEFAULT 'TZS',
  `signing_date` DATE NULL,
  `effective_date` DATE NULL,
  `expiry_date` DATE NULL,
  `renewal_date` DATE NULL,
  `status` ENUM('Draft', 'Under Review', 'Approved', 'Active', 'Expired', 'Terminated', 'Renewed') NOT NULL DEFAULT 'Draft',
  `document_path` VARCHAR(500) NULL,
  `document_name` VARCHAR(255) NULL,
  `payment_terms` VARCHAR(255) NULL,
  `renewal_terms` TEXT NULL,
  `termination_clause` TEXT NULL,
  `notify_before_expiry_days` INT DEFAULT 30,
  `notes` TEXT NULL,
  `managed_by` BIGINT UNSIGNED NULL,
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_contract_number` (`contract_number`),
  INDEX `idx_status` (`status`),
  INDEX `idx_expiry_date` (`expiry_date`),
  INDEX `idx_client_id` (`client_id`),
  INDEX `idx_managed_by` (`managed_by`),
  INDEX `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. COURT SCHEDULING
-- ============================================

CREATE TABLE IF NOT EXISTS `court_schedules` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_type` ENUM('Hearing', 'Filing Deadline', 'Court Appearance', 'Mediation', 'Arbitration', 'Meeting', 'Other') NOT NULL DEFAULT 'Hearing',
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `case_id` BIGINT UNSIGNED NULL,
  `case_number` VARCHAR(100) NULL,
  `court_name` VARCHAR(255) NULL,
  `court_room` VARCHAR(100) NULL,
  `judge_name` VARCHAR(255) NULL,
  `event_date` DATE NOT NULL,
  `event_time` TIME NULL,
  `duration_minutes` INT NULL DEFAULT 60,
  `location` VARCHAR(255) NULL,
  `status` ENUM('Scheduled', 'Confirmed', 'Postponed', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Scheduled',
  `priority` ENUM('Low', 'Medium', 'High', 'Urgent') NOT NULL DEFAULT 'Medium',
  `assigned_lawyer_id` BIGINT UNSIGNED NULL,
  `outcome` TEXT NULL,
  `notes` TEXT NULL,
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_event_date` (`event_date`),
  INDEX `idx_case_id` (`case_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_assigned_lawyer_id` (`assigned_lawyer_id`),
  INDEX `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. BILLING & FINANCE
-- ============================================

CREATE TABLE IF NOT EXISTS `legal_invoices` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` VARCHAR(100) NOT NULL UNIQUE,
  `client_id` BIGINT UNSIGNED NULL,
  `client_name` VARCHAR(255) NOT NULL,
  `case_id` BIGINT UNSIGNED NULL,
  `invoice_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `subtotal` DECIMAL(15, 2) NOT NULL DEFAULT 0,
  `tax_amount` DECIMAL(15, 2) DEFAULT 0,
  `total_amount` DECIMAL(15, 2) NOT NULL,
  `amount_paid` DECIMAL(15, 2) DEFAULT 0,
  `balance_due` DECIMAL(15, 2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'TZS',
  `status` ENUM('Draft', 'Sent', 'Paid', 'Partial', 'Overdue', 'Cancelled') NOT NULL DEFAULT 'Draft',
  `notes` TEXT NULL,
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_invoice_number` (`invoice_number`),
  INDEX `idx_status` (`status`),
  INDEX `idx_client_id` (`client_id`),
  INDEX `idx_case_id` (`case_id`),
  INDEX `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `legal_time_entries` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id` BIGINT UNSIGNED NULL,
  `client_id` BIGINT UNSIGNED NULL,
  `entry_date` DATE NOT NULL,
  `hours` DECIMAL(5, 2) NOT NULL,
  `description` TEXT NOT NULL,
  `hourly_rate` DECIMAL(10, 2) NOT NULL,
  `amount` DECIMAL(15, 2) NOT NULL,
  `billable` BOOLEAN DEFAULT TRUE,
  `billed` BOOLEAN DEFAULT FALSE,
  `lawyer_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_case_id` (`case_id`),
  INDEX `idx_lawyer_id` (`lawyer_id`),
  INDEX `idx_client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. DOCUMENT MANAGEMENT
-- ============================================

CREATE TABLE IF NOT EXISTS `legal_documents` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `document_name` VARCHAR(255) NOT NULL,
  `document_type` ENUM('Contract', 'Court Filing', 'Evidence', 'Letter', 'Agreement', 'Report', 'Other') NOT NULL DEFAULT 'Other',
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` BIGINT NULL,
  `case_id` BIGINT UNSIGNED NULL,
  `client_id` BIGINT UNSIGNED NULL,
  `contract_id` BIGINT UNSIGNED NULL,
  `status` ENUM('Draft', 'Under Review', 'Approved', 'Archived') NOT NULL DEFAULT 'Draft',
  `uploaded_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_case_id` (`case_id`),
  INDEX `idx_client_id` (`client_id`),
  INDEX `idx_contract_id` (`contract_id`),
  INDEX `idx_uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ADD FOREIGN KEY CONSTRAINTS
-- (Only if users table exists and has compatible structure)
-- ============================================

-- Add foreign keys for legal_clients
ALTER TABLE `legal_clients`
  ADD CONSTRAINT `fk_clients_lawyer` FOREIGN KEY (`assigned_lawyer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_clients_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Add foreign keys for legal_cases
ALTER TABLE `legal_cases`
  ADD CONSTRAINT `fk_cases_client` FOREIGN KEY (`client_id`) REFERENCES `legal_clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cases_lawyer` FOREIGN KEY (`assigned_lawyer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cases_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Add foreign keys for legal_contracts
ALTER TABLE `legal_contracts`
  ADD CONSTRAINT `fk_contracts_client` FOREIGN KEY (`client_id`) REFERENCES `legal_clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_contracts_managed` FOREIGN KEY (`managed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_contracts_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Add foreign keys for court_schedules
ALTER TABLE `court_schedules`
  ADD CONSTRAINT `fk_schedules_case` FOREIGN KEY (`case_id`) REFERENCES `legal_cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_schedules_lawyer` FOREIGN KEY (`assigned_lawyer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_schedules_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Add foreign keys for legal_invoices
ALTER TABLE `legal_invoices`
  ADD CONSTRAINT `fk_invoices_client` FOREIGN KEY (`client_id`) REFERENCES `legal_clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_invoices_case` FOREIGN KEY (`case_id`) REFERENCES `legal_cases` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_invoices_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Add foreign keys for legal_time_entries
ALTER TABLE `legal_time_entries`
  ADD CONSTRAINT `fk_time_case` FOREIGN KEY (`case_id`) REFERENCES `legal_cases` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_time_client` FOREIGN KEY (`client_id`) REFERENCES `legal_clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_time_lawyer` FOREIGN KEY (`lawyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- Add foreign keys for legal_documents
ALTER TABLE `legal_documents`
  ADD CONSTRAINT `fk_docs_case` FOREIGN KEY (`case_id`) REFERENCES `legal_cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_docs_client` FOREIGN KEY (`client_id`) REFERENCES `legal_clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_docs_contract` FOREIGN KEY (`contract_id`) REFERENCES `legal_contracts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_docs_uploaded` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Success message
SELECT 'Legal Management System - All tables and foreign keys created successfully!' AS status;
