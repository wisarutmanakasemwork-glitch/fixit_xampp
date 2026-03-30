<?php
// ==============================
// config.php — ตั้งค่าการเชื่อมต่อ MySQL
// แก้ไขค่าด้านล่างให้ตรงกับ XAMPP ของคุณ
// ==============================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // ค่าเริ่มต้นของ XAMPP
define('DB_PASS', '');           // ค่าเริ่มต้นของ XAMPP (ไม่มีรหัสผ่าน)
define('DB_NAME', 'fixit_kmutnb');
define('DB_CHARSET', 'utf8mb4');

// โฟลเดอร์เก็บรูปภาพ (ต้องอยู่ใน htdocs)
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'http://localhost/fixit_kmutnb/uploads/');

// ==============================
// สร้าง PDO Connection
// ==============================
function getDB(): PDO {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, DB_USER, DB_PASS, $options);
}

// ==============================
// ตั้งค่า CORS Headers
// ==============================
function setCorsHeaders(): void {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Content-Type: application/json; charset=UTF-8");
}

// ==============================
// ส่ง JSON Response
// ==============================
function jsonResponse(mixed $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
