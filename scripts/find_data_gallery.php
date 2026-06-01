<?php
$host = '127.0.0.1';
$db = 'karunya_printing';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$pdo = new PDO($dsn,$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

$stmt = $pdo->query("SELECT id_galeri_layanan AS id, judul, LEFT(lokasi_gambar,120) AS sample FROM tabel_galeri_layanan WHERE lokasi_gambar LIKE 'data:%' ORDER BY id_galeri_layanan DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!count($rows)) { echo "No gallery rows with data URI found.\n"; exit(0);} 
foreach($rows as $r){ echo "#{$r['id']} - {$r['judul']} - " . ($r['sample']?substr($r['sample'],0,80):'') . "\n"; }
