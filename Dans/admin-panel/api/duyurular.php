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

$method = $_SERVER['REQUEST_METHOD'];

// DB bağlantısı
require_once "db.php";
$pdo->exec("SET NAMES utf8mb4");

try {
    // GET - Duyuruları listele veya tek bir duyuruyu getir
    if ($method === "GET") {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT id, title, date, description, image FROM duyurular WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($announcement) {
                echo json_encode(["status" => "success", "data" => $announcement]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Duyuru bulunamadı."]);
            }
        } else {
            $stmt = $pdo->query("SELECT id, title, date, description, image FROM duyurular ORDER BY date DESC");
            $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["status" => "success", "data" => $announcements]);
        }
    }

    // POST - Yeni duyuru ekle
    elseif ($method === "POST") {
        $input = json_decode(file_get_contents("php://input"), true);
        if (empty($input) || !isset($input['title'], $input['date'], $input['description'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Eksik veri gönderimi."]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO duyurular (title, date, description, image) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([
            $input['title'],
            $input['date'],
            $input['description'],
            $input['image']
        ]);

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Duyuru başarıyla eklendi.", "id" => $pdo->lastInsertId()]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Duyuru eklenirken bir hata oluştu."]);
        }
    }

    // PUT - Duyuruyu güncelle
    elseif ($method === "PUT") {
        $input = json_decode(file_get_contents("php://input"), true);
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "ID gerekli."]);
            exit;
        }
        if (empty($input) || !isset($input['title'], $input['date'], $input['description'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Eksik veri gönderimi."]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE duyurular SET title=?, date=?, description=?, image=? WHERE id=?");
        $result = $stmt->execute([
            $input['title'],
            $input['date'],
            $input['description'],
            $input['image'],
            $input['id']
        ]);

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Duyuru başarıyla güncellendi."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Duyuru güncellenirken bir hata oluştu."]);
        }
    }

    // DELETE - Duyuruyu sil
    elseif ($method === "DELETE") {
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "ID gerekli."]);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM duyurular WHERE id=?");
        $result = $stmt->execute([$_GET['id']]);

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Duyuru başarıyla silindi."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Duyuru silinirken bir hata oluştu."]);
        }
    }

    // Varsayılan durum
    else {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "İzin verilmeyen HTTP yöntemi."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Veritabanı hatası: " . $e->getMessage()]);
}

// Bağlantıyı kapat
$pdo = null;
?>