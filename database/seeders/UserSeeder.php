<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Regular user
        User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'role' => 'user',
            'is_active' => true,
        ]);

        // Additional test users
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "Test User {$i}",
                'email' => "user{$i}@example.com",
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'role' => $i % 3 === 0 ? 'admin' : 'user',
                'is_active' => true,
            ]);
        }
    }
}
