<?php

namespace Database\Seeders;

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
        // Seed Admin user
        User::updateOrCreate(
            ['email' => 'admin@nobi.com'],
            [
                'name' => 'Admin Nobi',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        // Seed Operator user
        User::updateOrCreate(
            ['email' => 'operator@nobi.com'],
            [
                'name' => 'Operator Nobi',
                'password' => Hash::make('operator123'),
                'role' => 'operator',
            ]
        );
    }
}
