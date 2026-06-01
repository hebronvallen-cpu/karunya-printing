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

 $dir = __DIR__ . '/../public/temp_uploads';
$logoPath = $dir . '/logo.png';
if (!is_file($logoPath)) {
    echo "logo file not found at $logoPath\n";
    exit(1);
}
$mime = mime_content_type($logoPath) ?: 'image/png';
$b64 = base64_encode(file_get_contents($logoPath));
$dataUri = 'data:' . $mime . ';base64,' . $b64;

try {
    // try to increase max_allowed_packet if possible
    try {
        $pdo->exec('SET GLOBAL max_allowed_packet = 67108864');
        $pdo->exec('SET SESSION max_allowed_packet = 67108864');
        echo "Set GLOBAL and SESSION max_allowed_packet = 64MB\n";
    } catch (Exception $e) {
        echo "Could not set max_allowed_packet: " . $e->getMessage() . "\n";
    }

    $stmt = $pdo->prepare('INSERT INTO tabel_galeri_layanan (judul,kode_kategori,label_kategori,lokasi_gambar,urutan_tampil,aktif,waktu_dibuat,waktu_diperbarui) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())');
    $stmt->execute(['Logo', '', '', $dataUri, 0, 1]);
    echo "Inserted logo id: " . $pdo->lastInsertId() . "\n";
} catch (Exception $e) {
    echo "Insert failed: " . $e->getMessage() . "\n";
}
