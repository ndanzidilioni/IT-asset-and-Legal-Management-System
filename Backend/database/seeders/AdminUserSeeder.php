<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user with both name fields for compatibility
        User::create([
            'name' => 'Admin User',
            'fname' => 'Admin',
            'lname' => 'User',
            'email' => 'admin@example.com', 
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        echo "Admin user created: admin@example.com / password\n";
    }
}
