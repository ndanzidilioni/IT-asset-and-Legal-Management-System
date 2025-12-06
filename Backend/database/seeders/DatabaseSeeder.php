<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default users for login
        User::create([
            'fname' => 'System',
            'lname' => 'Administrator',
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'status' => 'active',
            'must_change_password' => true,
            'password_changed_at' => now(),
            'privileges' => []
        ]);

        User::create([
            'fname' => 'IT',
            'lname' => 'Developer',
            'email' => 'developer@system.com',
            'username' => 'developer',
            'password' => bcrypt('dev123'),
            'role' => 'developer',
            'status' => 'active',
            'must_change_password' => true,
            'password_changed_at' => now(),
            'privileges' => ['developer_access', 'manage_tasks', 'view_reports']
        ]);

        User::create([
            'fname' => 'System',
            'lname' => 'Client',
            'email' => 'client@system.com',
            'username' => 'client',
            'password' => bcrypt('client123'),
            'role' => 'client',
            'status' => 'active',
            'must_change_password' => true,
            'password_changed_at' => now(),
            'privileges' => ['client_access', 'view_reports']
        ]);

        // Seed dropdown options
        $this->call([
            DropdownOptionSeeder::class,
            ITAssetSeeder::class,
            DemandNotesSeeder::class,
        ]);
    }
}
