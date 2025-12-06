-- ============================================
-- DEMAND NOTES MANAGEMENT SYSTEM
-- MySQL Database Implementation
-- Created: November 7, 2025
-- ============================================

-- Use your database
USE `scheduling_management_system`;

-- ============================================
-- 1. Create demand_notes table
-- ============================================

CREATE TABLE IF NOT EXISTS `demand_notes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `demand_note_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique demand note identifier (DN-YYYY-####)',
  
  -- Client Information
  `client_name` VARCHAR(255) NOT NULL COMMENT 'Client full name or company name',
  `client_number` VARCHAR(255) NULL COMMENT 'Optional client reference number',
  `claim_reference` VARCHAR(255) NULL COMMENT 'Claim reference number',
  
  -- Financial Information
  `amount_claimed` DECIMAL(15, 2) NOT NULL COMMENT 'Total amount claimed in TZS',
  `amount_paid` DECIMAL(15, 2) NOT NULL DEFAULT 0.00 COMMENT 'Total amount paid so far',
  `balance_due` DECIMAL(15, 2) NOT NULL DEFAULT 0.00 COMMENT 'Remaining balance (auto-calculated)',
  `late_fee` DECIMAL(15, 2) NULL DEFAULT 0.00 COMMENT 'Late payment penalty fee',
  
  -- Dates
  `due_date` DATE NOT NULL COMMENT 'Payment due date',
  `issued_date` DATE NULL COMMENT 'Date demand note was issued',
  `payment_date` DATE NULL COMMENT 'Date when fully paid',
  
  -- Claim Details
  `nature_of_claim` ENUM('Works', 'Supply of Goods', 'Services', 'Consultancy', 'Other') NOT NULL DEFAULT 'Works' COMMENT 'Type of claim',
  `agency_of_claim` VARCHAR(255) NULL COMMENT 'Agency or organization name',
  
  -- Legal Notice & Settlement
  `notice_to_institute_suit` TEXT NULL COMMENT 'Legal notice details',
  `time_given_to_settle` VARCHAR(255) NULL COMMENT 'Settlement timeframe (e.g., 30 days)',
  `settlement_action_taken` TEXT NULL COMMENT 'Actions taken or settlement details',
  
  -- Status
  `current_status` ENUM('pending', 'paid', 'partially_paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Current payment status',
  
  -- Additional Information
  `remarks` TEXT NULL COMMENT 'Additional notes or comments',
  
  -- User Attribution
  `created_by` BIGINT UNSIGNED NULL COMMENT 'User ID who created this note',
  
  -- Timestamps
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  INDEX `idx_demand_note_number` (`demand_note_number`),
  INDEX `idx_client_name` (`client_name`),
  INDEX `idx_current_status` (`current_status`),
  INDEX `idx_due_date` (`due_date`),
  INDEX `idx_nature_of_claim` (`nature_of_claim`),
  INDEX `idx_created_by` (`created_by`),
  INDEX `idx_created_at` (`created_at`),
  
  -- Foreign Key Constraint
  CONSTRAINT `fk_demand_notes_created_by` 
    FOREIGN KEY (`created_by`) 
    REFERENCES `users` (`id`) 
    ON DELETE SET NULL 
    ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores demand notes for payment claims';

-- ============================================
-- 2. Create demand_note_payments table
-- ============================================

CREATE TABLE IF NOT EXISTS `demand_note_payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `demand_note_id` BIGINT UNSIGNED NOT NULL COMMENT 'Foreign key to demand_notes table',
  
  -- Payment Information
  `payment_amount` DECIMAL(15, 2) NOT NULL COMMENT 'Amount paid in this transaction',
  `payment_date` DATE NOT NULL COMMENT 'Date of payment',
  `payment_method` VARCHAR(100) NULL COMMENT 'Payment method (e.g., Bank Transfer, Cash)',
  `receipt_number` VARCHAR(255) NULL COMMENT 'Receipt or transaction number',
  `notes` TEXT NULL COMMENT 'Additional payment notes',
  
  -- User Attribution
  `recorded_by` BIGINT UNSIGNED NULL COMMENT 'User ID who recorded this payment',
  
  -- Timestamp
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  INDEX `idx_demand_note_id` (`demand_note_id`),
  INDEX `idx_payment_date` (`payment_date`),
  INDEX `idx_recorded_by` (`recorded_by`),
  
  -- Foreign Key Constraints
  CONSTRAINT `fk_payments_demand_note_id` 
    FOREIGN KEY (`demand_note_id`) 
    REFERENCES `demand_notes` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
    
  CONSTRAINT `fk_payments_recorded_by` 
    FOREIGN KEY (`recorded_by`) 
    REFERENCES `users` (`id`) 
    ON DELETE SET NULL 
    ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores payment records for demand notes';

-- ============================================
-- 3. Create trigger to update demand note totals on payment insert
-- ============================================

DELIMITER $$

CREATE TRIGGER `trg_after_payment_insert` 
AFTER INSERT ON `demand_note_payments`
FOR EACH ROW
BEGIN
  DECLARE total_paid DECIMAL(15, 2);
  DECLARE claimed_amount DECIMAL(15, 2);
  DECLARE late_fee_amount DECIMAL(15, 2);
  DECLARE new_balance DECIMAL(15, 2);
  DECLARE new_status VARCHAR(20);
  DECLARE due_date_val DATE;
  
  -- Calculate total amount paid for this demand note
  SELECT COALESCE(SUM(payment_amount), 0) INTO total_paid
  FROM demand_note_payments
  WHERE demand_note_id = NEW.demand_note_id;
  
  -- Get demand note details
  SELECT amount_claimed, COALESCE(late_fee, 0), due_date
  INTO claimed_amount, late_fee_amount, due_date_val
  FROM demand_notes
  WHERE id = NEW.demand_note_id;
  
  -- Calculate new balance
  SET new_balance = claimed_amount + late_fee_amount - total_paid;
  
  -- Determine new status
  IF new_balance <= 0 THEN
    SET new_status = 'paid';
  ELSEIF total_paid > 0 THEN
    SET new_status = 'partially_paid';
  ELSEIF due_date_val < CURDATE() THEN
    SET new_status = 'overdue';
  ELSE
    SET new_status = 'pending';
  END IF;
  
  -- Update demand note
  UPDATE demand_notes
  SET 
    amount_paid = total_paid,
    balance_due = new_balance,
    current_status = new_status,
    payment_date = IF(new_balance <= 0, CURDATE(), payment_date)
  WHERE id = NEW.demand_note_id;
  
END$$

DELIMITER ;

-- ============================================
-- 4. Create trigger to update demand note totals on payment delete
-- ============================================

DELIMITER $$

CREATE TRIGGER `trg_after_payment_delete` 
AFTER DELETE ON `demand_note_payments`
FOR EACH ROW
BEGIN
  DECLARE total_paid DECIMAL(15, 2);
  DECLARE claimed_amount DECIMAL(15, 2);
  DECLARE late_fee_amount DECIMAL(15, 2);
  DECLARE new_balance DECIMAL(15, 2);
  DECLARE new_status VARCHAR(20);
  DECLARE due_date_val DATE;
  
  -- Calculate total amount paid for this demand note
  SELECT COALESCE(SUM(payment_amount), 0) INTO total_paid
  FROM demand_note_payments
  WHERE demand_note_id = OLD.demand_note_id;
  
  -- Get demand note details
  SELECT amount_claimed, COALESCE(late_fee, 0), due_date
  INTO claimed_amount, late_fee_amount, due_date_val
  FROM demand_notes
  WHERE id = OLD.demand_note_id;
  
  -- Calculate new balance
  SET new_balance = claimed_amount + late_fee_amount - total_paid;
  
  -- Determine new status
  IF new_balance <= 0 THEN
    SET new_status = 'paid';
  ELSEIF total_paid > 0 THEN
    SET new_status = 'partially_paid';
  ELSEIF due_date_val < CURDATE() THEN
    SET new_status = 'overdue';
  ELSE
    SET new_status = 'pending';
  END IF;
  
  -- Update demand note
  UPDATE demand_notes
  SET 
    amount_paid = total_paid,
    balance_due = new_balance,
    current_status = new_status,
    payment_date = NULL
  WHERE id = OLD.demand_note_id;
  
END$$

DELIMITER ;

-- ============================================
-- 5. Create function to generate next demand note number
-- ============================================

DELIMITER $$

CREATE FUNCTION `generate_demand_note_number`()
RETURNS VARCHAR(50)
DETERMINISTIC
BEGIN
  DECLARE current_year INT;
  DECLARE next_number INT;
  DECLARE note_number VARCHAR(50);
  
  -- Get current year
  SET current_year = YEAR(CURDATE());
  
  -- Get the highest number for current year
  SELECT COALESCE(MAX(CAST(SUBSTRING(demand_note_number, 9) AS UNSIGNED)), 0) + 1
  INTO next_number
  FROM demand_notes
  WHERE demand_note_number LIKE CONCAT('DN-', current_year, '-%');
  
  -- Format as DN-YYYY-####
  SET note_number = CONCAT('DN-', current_year, '-', LPAD(next_number, 4, '0'));
  
  RETURN note_number;
END$$

DELIMITER ;

-- ============================================
-- 6. Create stored procedure to check and update overdue notes
-- ============================================

DELIMITER $$

CREATE PROCEDURE `update_overdue_demand_notes`()
BEGIN
  -- Update status to overdue for notes that are past due date and not fully paid
  UPDATE demand_notes
  SET current_status = 'overdue'
  WHERE due_date < CURDATE()
    AND current_status NOT IN ('paid', 'cancelled')
    AND balance_due > 0;
    
  -- Return count of updated notes
  SELECT ROW_COUNT() AS overdue_notes_updated;
END$$

DELIMITER ;

-- ============================================
-- 7. Insert sample data (optional - for testing)
-- ============================================

-- Uncomment to insert sample data

/*
INSERT INTO demand_notes (
  demand_note_number,
  client_name,
  client_number,
  claim_reference,
  amount_claimed,
  due_date,
  nature_of_claim,
  agency_of_claim,
  notice_to_institute_suit,
  time_given_to_settle,
  current_status,
  remarks,
  created_by,
  issued_date,
  balance_due
) VALUES 
(
  'DN-2025-0001',
  'ABC Construction Ltd',
  'CLT-2025-001',
  'REF-2025-001',
  10000000.00,
  '2025-12-31',
  'Works',
  'Ministry of Works',
  'Formal notice sent on 2025-10-01 requesting payment within 30 days',
  '30 days',
  'pending',
  'High priority claim for completed road construction project',
  (SELECT id FROM users WHERE role = 'lawyer' LIMIT 1),
  CURDATE(),
  10000000.00
),
(
  'DN-2025-0002',
  'XYZ Suppliers Ltd',
  'CLT-2025-002',
  'REF-2025-002',
  5000000.00,
  '2025-11-30',
  'Supply of Goods',
  'Tanzania Ports Authority',
  'Notice sent on 2025-09-15',
  '45 days',
  'pending',
  'Supply of office equipment and furniture',
  (SELECT id FROM users WHERE role = 'lawyer' LIMIT 1),
  CURDATE(),
  5000000.00
);
*/

-- ============================================
-- 8. Create view for demand notes summary
-- ============================================

CREATE OR REPLACE VIEW `vw_demand_notes_summary` AS
SELECT 
  dn.id,
  dn.demand_note_number,
  dn.client_name,
  dn.client_number,
  dn.amount_claimed,
  dn.amount_paid,
  dn.balance_due,
  dn.late_fee,
  dn.due_date,
  dn.issued_date,
  dn.nature_of_claim,
  dn.current_status,
  dn.created_at,
  u.username AS created_by_username,
  CONCAT(u.fname, ' ', u.lname) AS created_by_name,
  COUNT(dnp.id) AS payment_count,
  DATEDIFF(CURDATE(), dn.due_date) AS days_overdue
FROM demand_notes dn
LEFT JOIN users u ON dn.created_by = u.id
LEFT JOIN demand_note_payments dnp ON dn.id = dnp.demand_note_id
GROUP BY dn.id;

-- ============================================
-- 9. Create indexes for performance optimization
-- ============================================

-- Additional composite indexes for common queries
CREATE INDEX `idx_status_due_date` ON `demand_notes` (`current_status`, `due_date`);
CREATE INDEX `idx_nature_status` ON `demand_notes` (`nature_of_claim`, `current_status`);
CREATE INDEX `idx_client_status` ON `demand_notes` (`client_name`, `current_status`);

-- ============================================
-- SETUP COMPLETE
-- ============================================

-- Display success message
SELECT 
  'Demand Notes Management System database setup completed successfully!' AS status,
  (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'demand_notes') AS demand_notes_table_created,
  (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'demand_note_payments') AS payments_table_created,
  (SELECT COUNT(*) FROM information_schema.triggers WHERE trigger_schema = DATABASE() AND trigger_name LIKE 'trg_after_payment%') AS triggers_created,
  (SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema = DATABASE() AND routine_name IN ('generate_demand_note_number', 'update_overdue_demand_notes')) AS procedures_created;

-- ============================================
-- USAGE EXAMPLES
-- ============================================

/*

-- 1. Generate next demand note number
SELECT generate_demand_note_number() AS next_number;

-- 2. Check for overdue notes
CALL update_overdue_demand_notes();

-- 3. View demand notes summary
SELECT * FROM vw_demand_notes_summary WHERE current_status = 'overdue';

-- 4. Get statistics
SELECT 
  COUNT(*) AS total_notes,
  SUM(CASE WHEN current_status = 'pending' THEN 1 ELSE 0 END) AS pending,
  SUM(CASE WHEN current_status = 'paid' THEN 1 ELSE 0 END) AS paid,
  SUM(CASE WHEN current_status = 'overdue' THEN 1 ELSE 0 END) AS overdue,
  SUM(amount_claimed) AS total_claimed,
  SUM(amount_paid) AS total_collected,
  SUM(balance_due) AS total_outstanding
FROM demand_notes;

-- 5. Get payment history for a demand note
SELECT 
  dnp.*,
  u.username AS recorded_by_username
FROM demand_note_payments dnp
LEFT JOIN users u ON dnp.recorded_by = u.id
WHERE dnp.demand_note_id = 1
ORDER BY dnp.payment_date DESC;

*/
