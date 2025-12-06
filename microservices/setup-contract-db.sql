-- Contract Register Database Setup
CREATE DATABASE IF NOT EXISTS contract_register_db;
USE contract_register_db;

CREATE TABLE IF NOT EXISTS contracts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tender_number VARCHAR(100) NOT NULL UNIQUE,
    project_name VARCHAR(255) NOT NULL,
    supplier_contractor VARCHAR(255) NOT NULL,
    contract_amount DECIMAL(15, 2) NOT NULL,
    contract_year INT NOT NULL,
    date_received DATE NULL,
    date_vetted DATE NULL,
    date_signed DATE NULL,
    commencement_date DATE NULL,
    completion_date DATE NULL,
    document_path VARCHAR(500) NULL,
    document_name VARCHAR(255) NULL,
    status ENUM('Draft', 'Under Review', 'Vetted', 'Signed', 'Active', 'Completed', 'Terminated') DEFAULT 'Draft',
    notes TEXT NULL,
    created_by VARCHAR(255),
    updated_by VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_year (contract_year),
    INDEX idx_tender (tender_number),
    INDEX idx_status (status)
);

-- Insert sample data
INSERT INTO contracts (tender_number, project_name, supplier_contractor, contract_amount, contract_year, date_received, date_vetted, date_signed, commencement_date, completion_date, status, created_by) VALUES
('TEND-2024-001', 'Road Construction Project', 'ABC Construction Ltd', 500000000.00, 2024, '2024-01-15', '2024-02-10', '2024-02-20', '2024-03-01', '2024-12-31', 'Active', 'Admin'),
('TEND-2024-002', 'IT Infrastructure Upgrade', 'Tech Solutions Inc', 150000000.00, 2024, '2024-02-01', '2024-02-25', '2024-03-05', '2024-03-15', '2024-09-30', 'Active', 'Admin'),
('TEND-2023-045', 'Building Renovation', 'Modern Builders Co', 350000000.00, 2023, '2023-10-10', '2023-11-05', '2023-11-20', '2023-12-01', '2024-06-30', 'Completed', 'Admin'),
('TEND-2024-003', 'Security System Installation', 'SecureTech Ltd', 75000000.00, 2024, '2024-03-10', NULL, NULL, NULL, NULL, 'Under Review', 'Lawyer'),
('TEND-2024-004', 'Office Furniture Supply', 'FurniCorp Tanzania', 45000000.00, 2024, '2024-04-05', '2024-04-20', NULL, NULL, NULL, 'Vetted', 'Lawyer');

SELECT 'Contract Register database created successfully!' as message;
