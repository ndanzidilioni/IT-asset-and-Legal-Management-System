-- Update Contract Amount to Allow NULL Values
USE contract_register_db;

-- Modify contract_amount column to allow NULL
ALTER TABLE contracts 
MODIFY COLUMN contract_amount DECIMAL(15, 2) NULL;

SELECT 'Contract amount is now nullable!' as message;
