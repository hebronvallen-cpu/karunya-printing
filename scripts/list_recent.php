<?php
$host = '127.0.0.1';
$db = 'karunya_printing';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$pdo = new PDO($dsn,$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

echo "Recent gallery items:\n";
$stmt = $pdo->query('SELECT id_galeri_layanan AS id, judul, LEFT(lokasi_gambar,120) AS sample FROM tabel_galeri_layanan ORDER BY id_galeri_layanan DESC LIMIT 10');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r){ echo "#{$r['id']} - {$r['judul']} - " . ($r['sample']?substr($r['sample'],0,80):'') . "\n"; }

echo "\nAll home slides:\n";
$stmt = $pdo->query('SELECT id_banner_beranda AS id, judul, LEFT(lokasi_gambar,120) AS sample FROM tabel_banner_beranda ORDER BY id_banner_beranda DESC LIMIT 10');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r){ echo "#{$r['id']} - {$r['judul']} - " . ($r['sample']?substr($r['sample'],0,80):'') . "\n"; }
