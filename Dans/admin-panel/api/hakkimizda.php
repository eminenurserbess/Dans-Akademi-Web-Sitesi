<?php
// Hata raporlama
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS ve HTTP başlıkları
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// db.php bağlantısını dahil edin
require_once "db.php"; 
$pdo->exec("SET NAMES utf8mb4");

// Site URL'sini dinamik olarak belirleyin
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
           "://$_SERVER[HTTP_HOST]/";

// Hata mesajlarını ve başarı durumunu JSON formatında döndüren yardımcı fonksiyon
function sendResponse($success, $message, $data = [], $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode(["success" => $success, "message" => $message, "data" => $data]);
    exit;
}

// Güvenli dosya silme fonksiyonu
function deleteOldImage($pdo, $section_key) {
    $stmt = $pdo->prepare("SELECT image_url FROM hakkimizda WHERE section_key = ?");
    $stmt->execute([$section_key]);
    $oldImageUrl = $stmt->fetchColumn();

    if ($oldImageUrl && file_exists($oldImageUrl)) {
        unlink($oldImageUrl);
    }
}

try {
    // GET isteği: Verileri getir
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // name ve role sütunları kaldırıldı.
        $stmt = $pdo->prepare("SELECT section_key, title, description, image_url FROM hakkimizda");
        $stmt->execute();

        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Tam URL oluştur
            if ($row['image_url']) {
                $row['image_url'] = $baseUrl . $row['image_url'];
            }
            $data[$row['section_key']] = $row;
        }
        sendResponse(true, "Veriler başarıyla getirildi.", $data);
    }

    // POST isteği: Veri ekle veya güncelle
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $section_key = $_POST['section_key'] ?? null;
        if (!$section_key) {
            sendResponse(false, "section_key alanı zorunludur.", [], 400);
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image_url = $_POST['existing_image'] ?? null;
        $upload_dir = "uploads/hakkimizda/";

        // Dosya yükleme işlemleri ve güvenlik kontrolleri
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (!in_array($file_extension, $allowedExtensions)) {
                sendResponse(false, "Geçersiz dosya türü. Sadece JPG, JPEG, PNG, GIF ve WEBP desteklenir.", [], 415);
            }

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Benzersiz dosya adı oluştur
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                // Eski görseli sil
                deleteOldImage($pdo, $section_key);
                $image_url = $file_path;
            } else {
                sendResponse(false, "Dosya yüklenirken bir hata oluştu.", [], 500);
            }
        }

        // Veritabanında kayıt kontrolü
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM hakkimizda WHERE section_key = ?");
        $stmt_check->execute([$section_key]);
        $exists = $stmt_check->fetchColumn();

        if ($exists > 0) {
            // Kayıt varsa güncelleme
            // name ve role sütunları kaldırıldı.
            $stmt_update = $pdo->prepare("UPDATE hakkimizda SET title = ?, description = ?, image_url = ? WHERE section_key = ?");
            if ($stmt_update->execute([$title, $description, $image_url, $section_key])) {
                sendResponse(true, "Kayıt başarıyla güncellendi.");
            } else {
                throw new Exception("Veritabanı güncelleme hatası: " . implode(" ", $stmt_update->errorInfo()));
            }
        } else {
            // Kayıt yoksa ekleme
            // name ve role sütunları kaldırıldı.
            $stmt_insert = $pdo->prepare("INSERT INTO hakkimizda (section_key, title, description, image_url) VALUES (?, ?, ?, ?)");
            if ($stmt_insert->execute([$section_key, $title, $description, $image_url])) {
                sendResponse(true, "Yeni kayıt başarıyla eklendi.");
            } else {
                throw new Exception("Veritabanı ekleme hatası: " . implode(" ", $stmt_insert->errorInfo()));
            }
        }
    }

    // DELETE isteği: Kayıt sil
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // DELETE isteği için ID'yi URL'den alın
        $input = json_decode(file_get_contents("php://input"), true);
        $section_key = $input['section_key'] ?? null;
        if (!$section_key) {
            sendResponse(false, "section_key belirtilmedi.", [], 400);
        }

        // İlişkili görseli sil
        deleteOldImage($pdo, $section_key);

        $stmt = $pdo->prepare("DELETE FROM hakkimizda WHERE section_key = ?");
        if ($stmt->execute([$section_key])) {
            if ($stmt->rowCount() > 0) {
                sendResponse(true, "Kayıt başarıyla silindi.");
            } else {
                sendResponse(false, "Kayıt bulunamadı.", [], 404);
            }
        } else {
            throw new Exception("Silme işlemi hatası: " . implode(" ", $stmt->errorInfo()));
        }
    }

    // İstenmeyen bir HTTP metodu geldiğinde
    sendResponse(false, "Geçersiz HTTP metodu.", [], 405);

} catch (Exception $e) {
    // Hata kodunun bir tamsayı olduğunu kontrol et ve uygun HTTP kodunu gönder
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600 ? $e->getCode() : 500;
    sendResponse(false, $e->getMessage(), [], $httpCode);
}