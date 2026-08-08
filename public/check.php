<?php
echo "<h2>Sistem Diagnosis Cepat</h2>";

$baseDir = __DIR__ . '/../';

// Cek folder vendor
if (file_exists($baseDir . 'vendor/autoload.php')) {
    echo "✅ <b>Mesin Laravel (Vendor):</b> TERPASANG.<br>";
} else {
    echo "❌ <b>Mesin Laravel (Vendor):</b> HILANG / TIDAK TERPASANG! (Proses Composer gagal).<br>";
}

// Cek file .env
if (file_exists($baseDir . '.env')) {
    echo "✅ <b>File Konfigurasi (.env):</b> DITEMUKAN.<br>";
} else {
    echo "❌ <b>File Konfigurasi (.env):</b> HILANG / BELUM DIBUAT!<br>";
}

echo "<br><hr><i>Jika ada indikator tanda silang merah (❌), berarti itu penyebab Error 500-nya!</i>";
