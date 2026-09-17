<?php
// Veritabanı bağlantı ayarları artık .env dosyasından okunuyor

require_once __DIR__ . '/env.php';
loadEnv(__DIR__ . '/../.env');

$servername = getenv('DB_HOST');
$username   = getenv('DB_USERNAME');
$password   = getenv('DB_PASSWORD');
$dbname     = getenv('DB_NAME');

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>