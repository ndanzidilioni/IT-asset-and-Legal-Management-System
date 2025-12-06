<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user that must change password
        User::create([
            'fname' => 'Test',
            'lname' => 'User',
            'email' => 'testuser@example.com',
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'status' => 'active',
            'must_change_password' => true,
            'password_changed_at' => now(),
        ]);

        echo "Test user created: testuser@example.com / password123 (must change password)\n";
    }
}
