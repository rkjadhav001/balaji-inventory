<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $adminUser = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'phone' => '9876543210',
            'role' => 'admin',
            'password' => Hash::make('Admin@123'),
            'email_verified_at' => now(),
        ]);
    }
}
