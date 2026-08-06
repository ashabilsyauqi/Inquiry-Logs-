<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Lead;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        Lead::create([
            'name' => 'Pak Subroto',
            'phone' => '6287871976694',
            'stage' => 'Follow Up',
        ]);
    }
}
