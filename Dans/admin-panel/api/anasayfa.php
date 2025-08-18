<?php
// Hata raporlama ve HTTP başlıkları
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$method = $_SERVER['REQUEST_METHOD'];

// Veritabanı bağlantısı
require_once "db.php";
$pdo->exec("SET NAMES utf8mb4");

try {
    switch ($method) {
        case 'GET':
            $id = $_GET['id'] ?? null;
            if ($id) {
                // Tek bir kaydı getir
                $stmt = $pdo->prepare("SELECT * FROM vali_osman_dance_academy WHERE id = ?");
                $stmt->execute([$id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['data' => $result]);
                } else {
                    http_response_code(404);
                    echo json_encode(['message' => 'Record not found.']);
                }
            } else {
                // Tüm kayıtları getir
                $stmt = $pdo->query("SELECT * FROM vali_osman_dance_academy");
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                http_response_code(200);
                echo json_encode(['data' => $results]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            if ($data) {
                $sql = "INSERT INTO vali_osman_dance_academy (tur, baslik, aciklama, url, platform, adres, telefon, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $data['tur'],
                    $data['baslik'],
                    $data['aciklama'],
                    $data['url'],
                    $data['platform'],
                    $data['adres'],
                    $data['telefon'],
                    $data['email']
                ]);
                http_response_code(201);
                echo json_encode(['message' => 'Record created successfully.']);
            } else {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid input.']);
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents("php://input"), true);
            if ($data && isset($data['id'])) {
                $sql = "UPDATE vali_osman_dance_academy SET tur = ?, baslik = ?, aciklama = ?, url = ?, platform = ?, adres = ?, telefon = ?, email = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $data['tur'],
                    $data['baslik'],
                    $data['aciklama'],
                    $data['url'],
                    $data['platform'],
                    $data['adres'],
                    $data['telefon'],
                    $data['email'],
                    $data['id']
                ]);
                http_response_code(200);
                echo json_encode(['message' => 'Record updated successfully.']);
            } else {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid input or missing ID.']);
            }
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM vali_osman_dance_academy WHERE id = ?");
                $stmt->execute([$id]);
                http_response_code(200);
                echo json_encode(['message' => 'Record deleted successfully.']);
            } else {
                http_response_code(400);
                echo json_encode(['message' => 'Missing ID.']);
            }
            break;

        case 'OPTIONS':
            // Pre-flight isteği için
            http_response_code(200);
            exit();

        default:
            http_response_code(405);
            echo json_encode(['message' => 'Method not allowed.']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
}
?>