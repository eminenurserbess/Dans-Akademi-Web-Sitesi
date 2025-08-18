<?php
// Hata raporlama
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS ve HTTP başlıkları
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db.php";

try {
    // GET isteği: Verileri getir
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // prepare() ile SQL enjeksiyonuna karşı koruma
        $stmt = $pdo->prepare("SELECT id, adres, telefon, email FROM iletisim_bilgileri WHERE id = 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode(["success" => true, "data" => $row]);
        } else {
            // Eğer kayıt yoksa, boş bir veri seti döndür
            echo json_encode(["success" => true, "data" => ["adres" => "", "telefon" => "", "email" => ""]]);
        }
        exit;
    }

    // POST isteği: Veriyi güncelle
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // JSON body al
        $input = json_decode(file_get_contents("php://input"), true);

        $adres = $input['adres'] ?? '';
        $telefon = $input['telefon'] ?? '';
        $email = $input['email'] ?? '';

        if (empty($adres) || empty($telefon) || empty($email)) {
            throw new Exception("Adres, telefon ve e-posta alanları boş bırakılamaz.", 400);
        }

        // PDO'da num_rows yerine COUNT() kullanmak daha yaygındır ve etkilidir.
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM iletisim_bilgileri WHERE id = 1");
        $stmt_check->execute();
        $record_exists = $stmt_check->fetchColumn();

        if ($record_exists > 0) {
            // Kayıt varsa, güncelle
            $stmt_update = $pdo->prepare("UPDATE iletisim_bilgileri SET adres=?, telefon=?, email=? WHERE id=1");
            $stmt_update->execute([$adres, $telefon, $email]);
            echo json_encode(["success" => true, "message" => "İletişim bilgileri başarıyla güncellendi."]);
        } else {
            // Kayıt yoksa, ekle
            $stmt_insert = $pdo->prepare("INSERT INTO iletisim_bilgileri (id, adres, telefon, email) VALUES (1, ?, ?, ?)");
            $stmt_insert->execute([$adres, $telefon, $email]);
            echo json_encode(["success" => true, "message" => "İletişim bilgileri başarıyla eklendi."]);
        }
        exit;
    }

    throw new Exception("Geçersiz HTTP metodu.", 405);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}