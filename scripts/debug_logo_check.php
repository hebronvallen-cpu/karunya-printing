<?php
$host = '127.0.0.1';
$db = 'karunya_printing';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    echo "DB connect error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Gallery rows with data URI (lokasi_gambar LIKE 'data:%'):\n";
$stmt = $pdo->query("SELECT id_galeri_layanan AS id, judul, LENGTH(lokasi_gambar) AS len, LEFT(lokasi_gambar,120) AS sample FROM tabel_galeri_layanan WHERE lokasi_gambar LIKE 'data:%' ORDER BY id_galeri_layanan DESC LIMIT 20");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!count($rows)) { echo "  (none)\n"; }
foreach ($rows as $r) {
    echo "  #{$r['id']} - {$r['judul']} - len={$r['len']} - " . ($r['sample']?substr($r['sample'],0,80):'') . "\n";
}

echo "\nGallery rows with judul LIKE '%Logo%':\n";
$stmt = $pdo->query("SELECT id_galeri_layanan AS id, judul, LEFT(lokasi_gambar,120) AS sample FROM tabel_galeri_layanan WHERE judul LIKE '%Logo%' OR judul LIKE '%logo%' ORDER BY id_galeri_layanan DESC LIMIT 20");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!count($rows)) { echo "  (none)\n"; }
foreach ($rows as $r) {
    echo "  #{$r['id']} - {$r['judul']} - " . ($r['sample']?substr($r['sample'],0,80):'') . "\n";
}
