<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=scheduling', 'root', '');
    $stmt = $pdo->query('DESCRIBE audit_logs');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current audit_logs table structure:\n";
    echo "==================================\n";
    foreach($columns as $col) {
        echo $col['Field'] . ' - ' . $col['Type'] . "\n";
    }
    
    // Check if request_data column exists
    $hasRequestData = false;
    foreach($columns as $col) {
        if ($col['Field'] === 'request_data') {
            $hasRequestData = true;
            break;
        }
    }
    
    if (!$hasRequestData) {
        echo "\n❌ request_data column is missing!\n";
        echo "Adding request_data column...\n";
        
        $pdo->exec("ALTER TABLE audit_logs ADD COLUMN request_data TEXT NULL AFTER request_url");
        echo "✅ request_data column added successfully!\n";
    } else {
        echo "\n✅ request_data column exists!\n";
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}