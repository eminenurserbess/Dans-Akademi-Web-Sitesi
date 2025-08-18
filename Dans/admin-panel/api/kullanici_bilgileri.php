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

function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// GET isteği: Kullanıcıları listeleme veya tek kullanıcı getirme
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if ($user) {
            sendResponse($user);
        } else {
            sendResponse(['error' => 'Kullanıcı bulunamadı.'], 404);
        }
    } else {
        $stmt = $pdo->query("SELECT * FROM kullanicilar ORDER BY ad_soyad ASC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse($users);
    }
}

// POST isteği: Yeni kullanıcı ekleme
if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['ad_soyad'], $data['email'], $data['sifre'])) {
        $stmt = $pdo->prepare("INSERT INTO kullanicilar (ad_soyad, email, sifre, telefon, adres, odeme_bilgileri, kayitli_kurslar) VALUES (?, ?, ?, ?, ?, ?, ?)");
       $stmt->execute([$data['ad_soyad'], $data['email'], $data['sifre'], $data['telefon'], $data['adres'], $data['odeme_bilgileri'], $data['kayitli_kurslar']]);
        sendResponse(['message' => 'Kullanıcı başarıyla eklendi.', 'id' => $pdo->lastInsertId()], 201);
    } else {
        sendResponse(['error' => 'Zorunlu alanlar eksik.'], 400);
    }
}

// PUT isteği: Kullanıcı bilgilerini güncelleme
if ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['id'], $data['ad_soyad'], $data['email'])) {
        $sql = "UPDATE kullanicilar SET ad_soyad = ?, email = ?, telefon = ?, adres = ?, odeme_bilgileri = ?, kayitli_kurslar = ? WHERE id = ?";
        $params = [$data['ad_soyad'], $data['email'], $data['telefon'], $data['adres'], $data['odeme_bilgileri'], $data['kayitli_kurslar'], $data['id']];

        // Şifre alanı doluysa şifreyi de güncelle
        if (!empty($data['sifre'])) {
            // password_hash() fonksiyonunu kaldırın
            $sql = "UPDATE kullanicilar SET ad_soyad = ?, email = ?, sifre = ?, telefon = ?, adres = ?, odeme_bilgileri = ?, kayitli_kurslar = ? WHERE id = ?";
            array_splice($params, 2, 0, $data['sifre']); // Doğrudan şifreyi ekleyin
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        sendResponse(['message' => 'Kullanıcı başarıyla güncellendi.']);
    } else {
        sendResponse(['error' => 'Zorunlu alanlar eksik.'], 400);
    }
}

// DELETE isteği: Kullanıcı silme
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['id'])) {
        $stmt = $pdo->prepare("DELETE FROM kullanicilar WHERE id = ?");
        $stmt->execute([$data['id']]);
        if ($stmt->rowCount() > 0) {
            sendResponse(['message' => 'Kullanıcı başarıyla silindi.']);
        } else {
            sendResponse(['error' => 'Kullanıcı bulunamadı.'], 404);
        }
    } else {
        sendResponse(['error' => 'ID bilgisi eksik.'], 400);
    }
}


