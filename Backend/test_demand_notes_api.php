<?php

// Simple test script to check demand notes API directly
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

try {
    // Test database connection
    $pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
    echo "✅ Database connection successful\n";
    
    // Check if demand_notes table exists
    $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='demand_notes'");
    if ($result->fetch()) {
        echo "✅ demand_notes table exists\n";
        
        // Count records
        $count = $pdo->query("SELECT COUNT(*) FROM demand_notes")->fetchColumn();
        echo "📊 Found {$count} demand notes in database\n";
        
        if ($count > 0) {
            echo "📄 Sample demand notes:\n";
            $notes = $pdo->query("SELECT demand_note_number, client_name, current_status FROM demand_notes LIMIT 3")->fetchAll();
            foreach ($notes as $note) {
                echo "  - {$note['demand_note_number']}: {$note['client_name']} ({$note['current_status']})\n";
            }
        }
    } else {
        echo "❌ demand_notes table does not exist\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}