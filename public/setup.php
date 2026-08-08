<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<h2>🔧 Setup Otomatis Database CRM</h2><pre>";

try {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo "1. Migrasi Database:\n" . \Illuminate\Support\Facades\Artisan::output() . "\n";
    
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    echo "2. Bersihkan Cache:\n" . \Illuminate\Support\Facades\Artisan::output() . "\n";
    
    echo "\n✅ SETUP BERHASIL! Anda sudah bisa membuka crm.difitech.id\n";
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage();
}
echo "</pre>";
