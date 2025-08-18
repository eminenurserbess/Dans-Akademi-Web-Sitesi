<?php
// Hata raporlama
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS ve HTTP başlıkları
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Yöntem ve input verisi
$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents("php://input"), true);

// DB bağlantısı (Kendi db.php dosyanızın yolu)
require_once "db.php";
$pdo->exec("SET NAMES utf8mb4");

// GET - Etkinlikleri listele veya tek bir etkinliği getir
if ($method === "GET") {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM etkinlikler WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        // Durumu boolean'a çevir
        if ($event) {
            $event['status'] = (bool)$event['status'];
        }
        echo json_encode($event);
    } else {
        $stmt = $pdo->query("SELECT * FROM etkinlikler ORDER BY id DESC");
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Her etkinliğin durumunu boolean'a çevir
        foreach ($events as &$event) {
            $event['status'] = (bool)$event['status'];
        }
        echo json_encode($events);
    }
}

// POST - Yeni etkinlik ekle
elseif ($method === "POST") {
    $stmt = $pdo->prepare("INSERT INTO etkinlikler (name, datetime, location, description, image, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $input['name'],
        $input['datetime'],
        $input['location'],
        $input['description'],
        $input['image'],
        $input['status'] ? 1 : 0
    ]);
    echo json_encode(["success" => true, "id" => $pdo->lastInsertId()]);
}

// PUT - Etkinliği güncelle
elseif ($method === "PUT") {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "ID gerekli"]);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE etkinlikler SET name=?, datetime=?, location=?, description=?, image=?, status=? WHERE id=?");
    $stmt->execute([
        $input['name'],
        $input['datetime'],
        $input['location'],
        $input['description'],
        $input['image'],
        $input['status'] ? 1 : 0,
        $_GET['id']
    ]);
    echo json_encode(["success" => true]);
}

// DELETE - Etkinliği sil
elseif ($method === "DELETE") {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "ID gerekli"]);
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM etkinlikler WHERE id=?");
    $stmt->execute([$_GET['id']]);
    echo json_encode(["success" => true]);
}
?>