<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed default CEO Account
        User::updateOrCreate(
            ['role' => 'CEO'],
            [
                'name' => 'CEO / Owner (Wijaya)',
                'email' => 'wijaya@difitech.co.id',
                'password' => \Illuminate\Support\Facades\Hash::make('@Difitech2026'),
                'role' => 'CEO',
                'status' => 'APPROVED'
            ]
        );
    }
}
