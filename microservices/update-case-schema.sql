-- Update Case Management Database Schema
-- Add new fields for comprehensive case management

USE case_management_db;

-- Drop existing cases table and recreate with new schema
DROP TABLE IF EXISTS case_activities;
DROP TABLE IF EXISTS case_documents;
DROP TABLE IF EXISTS cases;

-- Create cases table with all required fields
CREATE TABLE cases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    case_number VARCHAR(50) UNIQUE NOT NULL,
    parties TEXT NOT NULL,
    nature_of_case ENUM('Civil', 'Criminal', 'Bankruptcy') NOT NULL,
    amount_in_claim DECIMAL(15, 2) NULL COMMENT 'Amount in TZS Shillings',
    date_filed DATE NULL,
    current_status ENUM('Pending Appeal', 'Pending Hearing', 'Active', 'Closed', 'Completed') DEFAULT 'Active',
    next_hearing_date DATE NULL,
    any_appeal ENUM('Yes', 'No', 'Pending') DEFAULT 'No',
    remarks TEXT NULL,
    
    -- Legacy fields (keeping for compatibility)
    title VARCHAR(255) NULL,
    client_name VARCHAR(255) NULL,
    case_type VARCHAR(100) NULL,
    priority ENUM('Low', 'Medium', 'High', 'Urgent') DEFAULT 'Medium',
    assigned_lawyer VARCHAR(255) NULL,
    description TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(255) NULL,
    
    INDEX idx_case_number (case_number),
    INDEX idx_nature (nature_of_case),
    INDEX idx_status (current_status),
    INDEX idx_next_hearing (next_hearing_date)
);

-- Create case_documents table for file uploads
CREATE TABLE case_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    case_id INT NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    file_path TEXT NOT NULL,
    file_size INT NULL,
    uploaded_by VARCHAR(255) NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT NULL,
    
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    INDEX idx_case_id (case_id)
);

-- Create case_activities table for audit trail
CREATE TABLE case_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    case_id INT NOT NULL,
    activity_type VARCHAR(100) NOT NULL,
    description TEXT,
    created_by VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    INDEX idx_case_id (case_id)
);

-- Insert sample cases with new structure
INSERT INTO cases (
    case_number, 
    parties, 
    nature_of_case, 
    amount_in_claim, 
    date_filed, 
    current_status, 
    next_hearing_date, 
    any_appeal, 
    remarks,
    assigned_lawyer
) VALUES 
(
    'CASE-2024-001',
    'John Smith (Plaintiff) vs. Johnson Corporation (Defendant)',
    'Civil',
    50000000.00,
    '2024-09-15',
    'Pending Hearing',
    '2024-11-03',
    'No',
    'Property dispute case regarding land ownership in Dar es Salaam',
    'Sarah Johnson'
),
(
    'CASE-2024-002',
    'The Republic vs. Michael Doe',
    'Criminal',
    NULL,
    '2024-10-01',
    'Pending Hearing',
    '2024-10-26',
    'No',
    'Criminal case - assault charges',
    'Mike Davis'
),
(
    'CASE-2024-003',
    'Acme Corporation (Creditor) vs. XYZ Limited (Debtor)',
    'Bankruptcy',
    150000000.00,
    '2024-10-10',
    'Pending Hearing',
    '2024-11-15',
    'No',
    'Corporate bankruptcy proceedings',
    'Lisa Chen'
),
(
    'CASE-2024-004',
    'Jane Doe (Appellant) vs. City Council (Respondent)',
    'Civil',
    25000000.00,
    '2024-09-20',
    'Pending Appeal',
    '2024-11-01',
    'Yes',
    'Appeal against city council decision on building permit',
    'Sarah Johnson'
),
(
    'CASE-2024-005',
    'The Republic vs. Robert Martinez',
    'Criminal',
    NULL,
    '2024-10-05',
    'Active',
    '2024-11-10',
    'No',
    'Criminal prosecution case',
    'Mike Davis'
);

-- Insert sample documents
INSERT INTO case_documents (case_id, document_name, document_type, file_path, uploaded_by) VALUES
(1, 'Initial Complaint', 'PDF', '/uploads/case-001-complaint.pdf', 'Sarah Johnson'),
(1, 'Evidence Photos', 'ZIP', '/uploads/case-001-evidence.zip', 'Sarah Johnson'),
(2, 'Police Report', 'PDF', '/uploads/case-002-police-report.pdf', 'Mike Davis'),
(3, 'Financial Statements', 'XLSX', '/uploads/case-003-financials.xlsx', 'Lisa Chen');

-- Insert sample activities
INSERT INTO case_activities (case_id, activity_type, description, created_by) VALUES
(1, 'Case Filed', 'Case filed with court registry', 'Sarah Johnson'),
(1, 'Document Submitted', 'Initial complaint document submitted', 'Sarah Johnson'),
(2, 'Case Filed', 'Criminal case filed', 'Mike Davis'),
(3, 'Case Filed', 'Bankruptcy petition filed', 'Lisa Chen');

SELECT 'Database schema updated successfully!' as message;
