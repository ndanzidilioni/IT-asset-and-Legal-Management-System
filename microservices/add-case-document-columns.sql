-- Add document columns to cases table
USE case_management_db;

ALTER TABLE cases 
ADD COLUMN document_path VARCHAR(255) NULL AFTER created_by,
ADD COLUMN document_name VARCHAR(255) NULL AFTER document_path;

SELECT 'Document columns added to cases table successfully!' as message;
