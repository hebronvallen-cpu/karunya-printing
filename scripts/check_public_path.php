<?php
chdir(__DIR__ . '/..');
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dir = public_path('temp_uploads');
echo "public_path('temp_uploads') = " . $dir . "\n";
$test = $dir . DIRECTORY_SEPARATOR . 'logo.png';
echo "Testing file: " . $test . "\n";
echo "exists: " . (is_file($test) ? 'true' : 'false') . "\n";
$items = glob($dir . DIRECTORY_SEPARATOR . '*');
if ($items === false) { echo "glob failed\n"; } else { echo "glob count: " . count($items) . "\n"; foreach ($items as $it) echo " - " . $it . "\n"; }
