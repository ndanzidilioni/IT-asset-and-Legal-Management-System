<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $pdo = DB::connection()->getPdo();
    echo "✓ Database connection successful!\n";
    echo "Connected to: " . config('database.default') . "\n";
    echo "Database: " . config('database.connections.'.config('database.default').'.database') . "\n";
    echo "Host: " . config('database.connections.'.config('database.default').'.host') . "\n";
    echo "Port: " . config('database.connections.'.config('database.default').'.port') . "\n";
} catch (\Exception $e) {
    echo "✗ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
}
