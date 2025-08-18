<?php
// Hata raporlama
ini_set('display_errors', 0); // Üretim için her zaman 0 olmalı
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');
error_reporting(E_ALL);

// CORS ve başlıklar
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// HTTP OPTIONS isteğini yönet
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// db.php bağlantısını dahil edin
require_once "db.php";
$pdo->exec("SET NAMES utf8mb4");

// Site URL'sini dinamik olarak belirleyin
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
           "://$_SERVER[HTTP_HOST]/";

/**
 * Veritabanı ve sunucudan yönetici görselini siler.
 * @param int $id Silinecek yöneticinin ID'si.
 * @param PDO $pdo Veritabanı bağlantısı.
 * @return bool İşlem başarılıysa true, değilse false.
 */
function deleteYoneticiImage($id, $pdo) {
    $stmt = $pdo->prepare("SELECT image_url FROM yoneticilerimiz WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['image_url'] && file_exists($row['image_url'])) {
        if (unlink($row['image_url'])) {
            return true;
        } else {
            throw new Exception("Dosya silinirken bir hata oluştu.", 500);
        }
    }
    return false;
}

try {
    // GET isteği: Verileri getir
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT id, full_name, role, description, image_url FROM yoneticilerimiz ORDER BY id DESC");
        $yoneticiler = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($yoneticiler as &$row) {
            if ($row['image_url']) {
                $row['image_url'] = $baseUrl . $row['image_url'];
            }
        }
        echo json_encode(["success" => true, "data" => ["yoneticiler" => $yoneticiler]]);
        exit;
    }

    // POST isteği: Yeni yönetici ekle
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST["name"] ?? "");
        $role = trim($_POST["role"] ?? "");
        $description = trim($_POST["description"] ?? "");
        if (!$name || !$role || !$description) {
            throw new Exception("Tüm alanları doldurun.", 400);
        }

        $image_url = null;
        if (!empty($_FILES["image"]["name"])) {
            $targetDir = "uploads/yoneticiler/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $allowed = ["jpg", "jpeg", "png", "gif", "webp"];
            if (!in_array($ext, $allowed)) {
                throw new Exception("Geçersiz dosya türü.", 415);
            }

            $fileName = uniqid() . "." . $ext;
            $targetPath = $targetDir . $fileName;

            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
                throw new Exception("Dosya yüklenemedi.", 500);
            }
            $image_url = $targetPath;
        }

        $stmt = $pdo->prepare("INSERT INTO yoneticilerimiz (full_name, role, description, image_url) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $role, $description, $image_url])) {
            $last_id = $pdo->lastInsertId();
            $image_full_url = $image_url ? $baseUrl . $image_url : null;
            echo json_encode([
                "success" => true,
                "message" => "Yeni yönetici eklendi.",
                "data" => ["id" => $last_id, "full_name" => $name, "role" => $role, "description" => $description, "image_url" => $image_full_url]
            ]);
        } else {
            throw new Exception("Veritabanı ekleme hatası.", 500);
        }
        exit;
    }

    // PUT isteği: Yönetici güncelle
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        parse_str(file_get_contents("php://input"), $_PUT);
        $id = $_PUT['id'] ?? null;
        if (!$id) {
            throw new Exception("Güncellenecek yönetici ID'si belirtilmedi.", 400);
        }
        
        $name = trim($_PUT["name"] ?? "");
        $role = trim($_PUT["role"] ?? "");
        $description = trim($_PUT["description"] ?? "");

        // Dosya yükleme işlemleri
        $image_url = $_PUT['existing_image'] ?? null;
        if (!empty($_FILES["image"]["name"])) {
            $targetDir = "uploads/yoneticiler/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $allowed = ["jpg", "jpeg", "png", "gif", "webp"];
            if (!in_array($ext, $allowed)) {
                throw new Exception("Geçersiz dosya türü.", 415);
            }

            $fileName = uniqid() . "." . $ext;
            $targetPath = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
                deleteYoneticiImage($id, $pdo);
                $image_url = $targetPath;
            } else {
                throw new Exception("Dosya yüklenemedi.", 500);
            }
        }
        
        $stmt = $pdo->prepare("UPDATE yoneticilerimiz SET full_name = ?, role = ?, description = ?, image_url = ? WHERE id = ?");
        if ($stmt->execute([$name, $role, $description, $image_url, $id])) {
            if ($stmt->rowCount() > 0) {
                $image_full_url = $image_url ? $baseUrl . $image_url : null;
                echo json_encode([
                    "success" => true,
                    "message" => "Yönetici başarıyla güncellendi.",
                    "data" => ["id" => $id, "full_name" => $name, "role" => $role, "description" => $description, "image_url" => $image_full_url]
                ]);
            } else {
                echo json_encode(["success" => true, "message" => "Herhangi bir değişiklik yapılmadı."]);
            }
        } else {
            throw new Exception("Veritabanı güncelleme hatası.", 500);
        }
        exit;
    }

     // DELETE isteği: Yönetici sil
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
       // Önce JSON'dan ID'yi almayı dene
        $input = json_decode(file_get_contents("php://input"), true);
        $id = $input['id'] ?? null;
        
        // Eğer JSON'da ID yoksa, URL parametresinden almayı dene
        if (!$id && isset($_GET['id'])) {
            $id = $_GET['id'];
        }
        
        if (!$id) {
            throw new Exception("ID belirtilmedi.", 400);
        }

        deleteYoneticiImage($id, $pdo);
        
        $stmt = $pdo->prepare("DELETE FROM yoneticilerimiz WHERE id = ?");
        if ($stmt->execute([$id])) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(["success" => true, "message" => "Yönetici başarıyla silindi."]);
            } else {
                throw new Exception("Yönetici bulunamadı.", 404);
            }
        } else {
            throw new Exception("Silme işlemi hatası.", 500);
        }
        exit;
    }

    throw new Exception("İzin verilmeyen HTTP metodu.", 405);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
