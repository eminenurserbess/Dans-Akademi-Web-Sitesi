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

// DB bağlantısı
require_once "db.php"; 
$pdo->exec("SET NAMES utf8mb4");

// GET - Kursları listele
if ($method === "GET") {
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM kurslar WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        // Durumu boolean'a çevir
        $course['status'] = $course['status'] == 1 ? true : false;
        echo json_encode($course);
    } else {
        $stmt = $pdo->query("SELECT * FROM kurslar ORDER BY id DESC");
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Her kursun durumunu boolean'a çevir
        foreach ($courses as &$course) {
            $course['status'] = $course['status'] == 1 ? true : false;
        }
        echo json_encode($courses);
    }
}

// POST - Yeni kurs ekle
elseif ($method === "POST") {
    $stmt = $pdo->prepare("INSERT INTO kurslar (name, instructor, capacity, description, image, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $input['name'],
        $input['instructor'],
        $input['capacity'],
        $input['description'],
        $input['image'],
        $input['status'] ? 1 : 0
    ]);
    echo json_encode(["success" => true, "id" => $pdo->lastInsertId()]);
}

// PUT - Kurs güncelle
elseif ($method === "PUT") {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "ID gerekli"]);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE kurslar SET name=?, instructor=?, capacity=?, description=?, image=?, status=? WHERE id=?");
    $stmt->execute([
        $input['name'],
        $input['instructor'],
        $input['capacity'],
        $input['description'],
        $input['image'],
        $input['status'] ? 1 : 0,
        $_GET['id']
    ]);
    echo json_encode(["success" => true]);
}

// DELETE - Kurs sil
elseif ($method === "DELETE") {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "ID gerekli"]);
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM kurslar WHERE id=?");
    $stmt->execute([$_GET['id']]);
    echo json_encode(["success" => true]);
}
