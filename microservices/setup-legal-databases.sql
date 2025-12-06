-- Legal Management System Database Setup
-- Run this script to create all databases and tables

-- ============================================
-- CASE MANAGEMENT DATABASE
-- ============================================
CREATE DATABASE IF NOT EXISTS case_management_db;
USE case_management_db;

CREATE TABLE IF NOT EXISTS cases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    case_number VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    client_id INT,
    case_type ENUM('Civil', 'Criminal', 'Corporate', 'Family', 'Immigration', 'Personal Injury', 'Real Estate', 'Employment', 'Other') NOT NULL,
    status ENUM('Active', 'Pending', 'Completed', 'Closed', 'Urgent') DEFAULT 'Active',
    priority ENUM('Low', 'Medium', 'High', 'Urgent') DEFAULT 'Medium',
    filed_date DATE NOT NULL,
    assigned_lawyer VARCHAR(255),
    lawyer_id INT,
    next_hearing DATE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS case_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    case_id INT NOT NULL,
    activity_type VARCHAR(100),
    description TEXT,
    created_by VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
);

-- Insert sample cases
INSERT INTO cases (case_number, title, client_name, case_type, status, priority, filed_date, assigned_lawyer, next_hearing, description) VALUES
('CASE-2024-001', 'Smith vs. Johnson', 'John Smith', 'Civil', 'Active', 'High', '2024-09-15', 'Sarah Johnson', '2024-11-03', 'Civil litigation case regarding property dispute'),
('CASE-2024-002', 'Doe Family Matter', 'Jane Doe', 'Family', 'Urgent', 'Urgent', '2024-10-01', 'Mike Davis', '2024-10-26', 'Family law case - custody matter'),
('CASE-2024-003', 'Acme Corp Contract Dispute', 'Acme Corporation', 'Corporate', 'Pending', 'Medium', '2024-10-10', 'Lisa Chen', '2024-11-15', 'Corporate contract dispute'),
('CASE-2024-004', 'Immigration Application', 'Robert Martinez', 'Immigration', 'Active', 'High', '2024-09-20', 'Sarah Johnson', '2024-11-01', 'Immigration visa application'),
('CASE-2024-005', 'Personal Injury Claim', 'Emily Brown', 'Personal Injury', 'Active', 'Medium', '2024-10-05', 'Mike Davis', '2024-11-10', 'Personal injury compensation claim');

-- ============================================
-- CLIENT MANAGEMENT DATABASE
-- ============================================
CREATE DATABASE IF NOT EXISTS client_management_db;
USE client_management_db;

CREATE TABLE IF NOT EXISTS clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(50),
    address TEXT,
    client_since DATE NOT NULL,
    status ENUM('Active', 'Inactive', 'VIP') DEFAULT 'Active',
    active_cases INT DEFAULT 0,
    total_cases INT DEFAULT 0,
    outstanding_balance DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample clients
INSERT INTO clients (name, email, phone, address, client_since, status, active_cases, total_cases, outstanding_balance) VALUES
('John Smith', 'john.smith@email.com', '+1 (555) 123-4567', '123 Main St, City, State', '2024-01-15', 'Active', 2, 3, 5250.00),
('Jane Doe', 'jane.doe@email.com', '+1 (555) 234-5678', '456 Oak Ave, City, State', '2024-03-20', 'Active', 1, 1, 3800.00),
('Acme Corporation', 'legal@acmecorp.com', '+1 (555) 345-6789', '789 Business Blvd, City, State', '2023-11-10', 'VIP', 3, 8, 15000.00),
('Robert Martinez', 'robert.m@email.com', '+1 (555) 456-7890', '321 Pine Rd, City, State', '2024-05-12', 'Active', 1, 2, 2500.00),
('Emily Brown', 'emily.brown@email.com', '+1 (555) 567-8901', '654 Elm St, City, State', '2024-06-08', 'Active', 1, 1, 4200.00);

-- ============================================
-- DOCUMENT MANAGEMENT DATABASE
-- ============================================
CREATE DATABASE IF NOT EXISTS document_management_db;
USE document_management_db;

CREATE TABLE IF NOT EXISTS documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    case_number VARCHAR(50),
    case_id INT,
    type VARCHAR(50),
    size VARCHAR(20),
    created_by VARCHAR(255),
    status ENUM('Draft', 'Pending', 'Approved', 'Signed', 'Archived') DEFAULT 'Draft',
    version VARCHAR(20) DEFAULT '1.0',
    category VARCHAR(100),
    file_path TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample documents
INSERT INTO documents (name, case_number, type, size, created_by, status, version, category) VALUES
('Motion to Dismiss', 'CASE-2024-001', 'PDF', '2.4 MB', 'Sarah Johnson', 'Signed', '2.1', 'Pleadings'),
('Client Agreement', 'CASE-2024-001', 'PDF', '1.8 MB', 'Mike Davis', 'Signed', '1.0', 'Contracts'),
('Evidence Photo', 'CASE-2024-002', 'JPG', '3.2 MB', 'Lisa Chen', 'Approved', '1.0', 'Evidence'),
('Witness Statement', 'CASE-2024-003', 'DOC', '0.8 MB', 'Sarah Johnson', 'Approved', '1.0', 'Evidence'),
('Court Filing', 'CASE-2024-004', 'PDF', '1.5 MB', 'Mike Davis', 'Pending', '1.0', 'Pleadings');

-- ============================================
-- COURT SCHEDULING DATABASE
-- ============================================
CREATE DATABASE IF NOT EXISTS court_scheduling_db;
USE court_scheduling_db;

CREATE TABLE IF NOT EXISTS hearings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    case_number VARCHAR(50) NOT NULL,
    case_id INT,
    type VARCHAR(100),
    hearing_date DATE NOT NULL,
    hearing_time TIME NOT NULL,
    court VARCHAR(255),
    judge VARCHAR(255),
    room VARCHAR(50),
    status ENUM('Scheduled', 'Postponed', 'Completed', 'Cancelled', 'Urgent') DEFAULT 'Scheduled',
    assigned_lawyer VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS deadlines (
    id INT PRIMARY KEY AUTO_INCREMENT,
    case_number VARCHAR(50) NOT NULL,
    case_id INT,
    deadline_type VARCHAR(100),
    deadline_date DATE NOT NULL,
    priority ENUM('Low', 'Medium', 'High', 'Urgent') DEFAULT 'Medium',
    status ENUM('Pending', 'Completed', 'Overdue') DEFAULT 'Pending',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample hearings
INSERT INTO hearings (case_number, type, hearing_date, hearing_time, court, judge, room, status, assigned_lawyer) VALUES
('CASE-2024-001', 'Court Hearing', '2024-11-03', '10:00:00', 'Downtown District Court', 'Hon. Maria Rodriguez', 'Courtroom 3A', 'Scheduled', 'Sarah Johnson'),
('CASE-2024-002', 'Pre-Trial Conference', '2024-10-26', '14:00:00', 'Family Court', 'Hon. James Anderson', 'Courtroom 1B', 'Urgent', 'Mike Davis'),
('CASE-2024-003', 'Status Conference', '2024-11-15', '09:30:00', 'Commercial Court', 'Hon. Patricia Wilson', 'Courtroom 2C', 'Scheduled', 'Lisa Chen');

-- Insert sample deadlines
INSERT INTO deadlines (case_number, deadline_type, deadline_date, priority) VALUES
('CASE-2024-001', 'Motion Due', '2024-10-25', 'High'),
('CASE-2024-002', 'Document Filing', '2024-10-28', 'Urgent'),
('CASE-2024-003', 'Discovery Deadline', '2024-11-15', 'Medium');

-- ============================================
-- BILLING & FINANCE DATABASE
-- ============================================
CREATE DATABASE IF NOT EXISTS billing_finance_db;
USE billing_finance_db;

CREATE TABLE IF NOT EXISTS invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    client_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('Paid', 'Pending', 'Overdue', 'Cancelled') DEFAULT 'Pending',
    due_date DATE NOT NULL,
    created_date DATE NOT NULL,
    paid_date DATE,
    billable_hours DECIMAL(5, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS time_entries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lawyer VARCHAR(255) NOT NULL,
    lawyer_id INT,
    case_number VARCHAR(50),
    case_id INT,
    hours DECIMAL(5, 2) NOT NULL,
    description TEXT,
    entry_date DATE NOT NULL,
    hourly_rate DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample invoices
INSERT INTO invoices (invoice_number, client_name, amount, status, due_date, created_date, billable_hours) VALUES
('INV-2024-101', 'John Smith', 5250.00, 'Pending', '2024-11-01', '2024-10-15', 21.0),
('INV-2024-100', 'Jane Doe', 3800.00, 'Paid', '2024-10-20', '2024-10-01', 15.2),
('INV-2024-099', 'Acme Corporation', 12500.00, 'Paid', '2024-10-15', '2024-09-25', 50.0),
('INV-2024-098', 'Robert Martinez', 2500.00, 'Pending', '2024-10-30', '2024-10-10', 10.0);

-- Insert sample time entries
INSERT INTO time_entries (lawyer, case_number, hours, description, entry_date, hourly_rate) VALUES
('Sarah Johnson', 'CASE-2024-001', 3.5, 'Legal research', '2024-10-24', 250.00),
('Mike Davis', 'CASE-2024-002', 2.0, 'Client meeting', '2024-10-24', 250.00),
('Lisa Chen', 'CASE-2024-003', 1.5, 'Document review', '2024-10-24', 250.00),
('Sarah Johnson', 'CASE-2024-004', 2.5, 'Court preparation', '2024-10-24', 250.00);

-- ============================================
-- COMPLIANCE & SECURITY DATABASE
-- ============================================
CREATE DATABASE IF NOT EXISTS compliance_security_db;
USE compliance_security_db;

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    log_time TIME NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    action VARCHAR(255) NOT NULL,
    resource VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample audit logs
INSERT INTO audit_logs (log_time, user_name, action, resource, ip_address) VALUES
('10:45:00', 'Sarah Johnson', 'Viewed', 'CASE-001', '192.168.1.100'),
('10:30:00', 'Mike Davis', 'Updated', 'CASE-002', '192.168.1.101'),
('10:15:00', 'John Smith', 'Signed Document', 'DOC-456', '192.168.1.102'),
('09:50:00', 'Lisa Chen', 'Created Case', 'CASE-099', '192.168.1.103');

-- ============================================
-- LEGAL ANALYTICS DATABASE
-- ============================================
CREATE DATABASE IF NOT EXISTS legal_analytics_db;
USE legal_analytics_db;

CREATE TABLE IF NOT EXISTS lawyer_performance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lawyer_name VARCHAR(255) NOT NULL,
    lawyer_id INT,
    total_hours DECIMAL(10, 2) DEFAULT 0,
    total_cases INT DEFAULT 0,
    total_revenue DECIMAL(15, 2) DEFAULT 0,
    month_year VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample performance data
INSERT INTO lawyer_performance (lawyer_name, total_hours, total_cases, total_revenue, month_year) VALUES
('Sarah Johnson', 245, 15, 98000, '2024-10'),
('Mike Davis', 198, 12, 79000, '2024-10'),
('Lisa Chen', 167, 10, 67000, '2024-10');
