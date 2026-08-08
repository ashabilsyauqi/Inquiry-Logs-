<?php
set_time_limit(300);
ini_set('display_errors', 1);
echo "<h2>🛠️ Instalasi Mesin Laravel Otomatis</h2><pre>";

$baseDir = realpath(__DIR__ . '/../');

if (!file_exists('composer.phar')) {
    echo "1. Mendownload program Composer dari server resmi...\n";
    file_put_contents('composer.phar', file_get_contents('https://getcomposer.org/download/latest-stable/composer.phar'));
}

echo "2. Menjalankan proses instalasi (Harap tunggu 1-2 menit, loading mungkin agak lama)...\n\n";
putenv('COMPOSER_HOME=' . $baseDir . '/.composer');

if (function_exists('shell_exec')) {
    $output = shell_exec('php composer.phar install --no-dev --optimize-autoloader --working-dir="' . $baseDir . '" 2>&1');
    echo $output;
} else {
    echo "❌ Fitur eksekusi otomatis (shell_exec) dikunci oleh hosting Anda. Harap hubungi penyedia hosting untuk mengaktifkannya.\n";
}

if (file_exists($baseDir . '/vendor/autoload.php')) {
    echo "\n\n✅ SUKSES BESAAR!! Mesin Laravel (Vendor) berhasil terpasang sempurna!\n";
    echo "Selanjutnya, silakan klik link ini: <a href='/setup.php'>Jalankan Setup Database >></a>";
} else {
    echo "\n\n❌ GAGAL! Mesin belum terpasang. Beri tahu teknisi AI Anda tentang pesan error di atas.\n";
}
echo "</pre>";
