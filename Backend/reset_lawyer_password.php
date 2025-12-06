<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('username', 'lawyer')->first();

if ($user) {
    $user->password = Hash::make('password');
    $user->must_change_password = false;
    $user->status = 'active';
    $user->save();
    
    echo "✅ Password updated for lawyer user\n";
    echo "Username: " . $user->username . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Role: " . $user->role . "\n";
    echo "New Password: password\n";
} else {
    echo "❌ Lawyer user not found\n";
}
