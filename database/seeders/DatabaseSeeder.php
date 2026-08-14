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
        // Seed default CEO Account if not exists
        User::firstOrCreate(
            ['email' => 'ceo@difitech.id'],
            [
                'name' => 'CEO / Owner',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'CEO',
                'status' => 'APPROVED'
            ]
        );
    }
}
