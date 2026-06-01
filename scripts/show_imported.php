<?php
// Show imported Logo and first HomeSlide sample
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

// Logo
$stmt = $pdo->prepare('SELECT id_galeri_layanan AS id, judul, LEFT(lokasi_gambar,200) AS sample FROM tabel_galeri_layanan WHERE judul LIKE ? ORDER BY waktu_dibuat DESC LIMIT 1');
$stmt->execute(['%Logo%']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    echo "Gallery Logo ID: " . $row['id'] . "\n";
    echo "Judul: " . $row['judul'] . "\n";
    echo "Sample lokasi_gambar: " . $row['sample'] . "\n\n";
} else {
    echo "No Gallery Logo found.\n\n";
}

// HomeSlide
$stmt = $pdo->query('SELECT id_banner_beranda AS id, judul, LEFT(lokasi_gambar,200) AS sample FROM tabel_banner_beranda ORDER BY id_banner_beranda LIMIT 1');
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    echo "HomeSlide ID: " . $row['id'] . "\n";
    echo "Judul: " . $row['judul'] . "\n";
    echo "Sample lokasi_gambar: " . $row['sample'] . "\n";
} else {
    echo "No HomeSlide found.\n";
}
