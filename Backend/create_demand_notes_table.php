<?php

// Direct SQL to create demand notes tables
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating demand_notes table...\n";
    
    // Create demand_notes table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS demand_notes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            demand_note_number VARCHAR(255) NOT NULL,
            client_name VARCHAR(255) NOT NULL,
            client_number VARCHAR(255),
            claim_reference VARCHAR(255),
            amount_claimed DECIMAL(15,2) NOT NULL,
            amount_paid DECIMAL(15,2) DEFAULT 0,
            balance_due DECIMAL(15,2) DEFAULT 0,
            due_date DATE NOT NULL,
            nature_of_claim VARCHAR(255) NOT NULL,
            agency_of_claim VARCHAR(255),
            notice_to_institute_suit TEXT,
            time_given_to_settle VARCHAR(255),
            settlement_action_taken TEXT,
            current_status VARCHAR(50) DEFAULT 'pending',
            remarks TEXT,
            late_fee DECIMAL(15,2) DEFAULT 0,
            issued_date DATE,
            created_by INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    echo "✅ demand_notes table created\n";
    
    // Create demand_note_payments table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS demand_note_payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            demand_note_id INTEGER NOT NULL,
            payment_amount DECIMAL(15,2) NOT NULL,
            payment_date DATE NOT NULL,
            payment_method VARCHAR(100),
            receipt_number VARCHAR(255),
            notes TEXT,
            recorded_by INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (demand_note_id) REFERENCES demand_notes(id) ON DELETE CASCADE
        )
    ");
    
    echo "✅ demand_note_payments table created\n";
    
    // Create some sample data
    $pdo->exec("
        INSERT OR IGNORE INTO demand_notes (
            demand_note_number, client_name, client_number, claim_reference,
            amount_claimed, due_date, nature_of_claim, current_status
        ) VALUES 
        ('DN-2025-001', 'ABC Construction Ltd', 'CL001', 'REF001', 500000.00, '2025-01-15', 'Works', 'pending'),
        ('DN-2025-002', 'XYZ Suppliers Ltd', 'CL002', 'REF002', 250000.00, '2025-01-20', 'Supply of Goods', 'paid'),
        ('DN-2025-003', 'DEF Services Ltd', 'CL003', 'REF003', 750000.00, '2025-01-10', 'Services', 'overdue')
    ");
    
    echo "✅ Sample demand notes created\n";
    
    // Verify
    $count = $pdo->query("SELECT COUNT(*) FROM demand_notes")->fetchColumn();
    echo "📊 Total demand notes: {$count}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}