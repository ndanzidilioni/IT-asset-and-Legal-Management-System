-- Update Client Management Database Schema
-- Add new fields for comprehensive client management

USE client_management_db;

-- Add missing columns to clients table
ALTER TABLE clients 
ADD COLUMN client_type ENUM('Individual', 'Corporate', 'Government', 'NGO') DEFAULT 'Individual' AFTER phone;

ALTER TABLE clients 
ADD COLUMN company VARCHAR(255) NULL AFTER client_type;

ALTER TABLE clients 
ADD COLUMN id_number VARCHAR(100) NULL AFTER company;

ALTER TABLE clients 
ADD COLUMN notes TEXT NULL AFTER outstanding_balance;

-- Make email nullable (was required but we want it optional)
ALTER TABLE clients 
MODIFY COLUMN email VARCHAR(255) NULL;

-- Make client_since auto-generated
ALTER TABLE clients 
MODIFY COLUMN client_since DATE DEFAULT (CURRENT_DATE);

-- Update existing records
UPDATE clients SET client_type = 'Individual' WHERE client_type IS NULL OR client_type = '';

SELECT 'Client table schema updated successfully!' as message;
