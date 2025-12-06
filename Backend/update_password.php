<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'admin@system.com')->first();
if ($user) {
    $user->password = Hash::make('password');
    $user->save();
    echo "Password updated for admin@system.com\n";
} else {
    echo "User not found\n";
}
