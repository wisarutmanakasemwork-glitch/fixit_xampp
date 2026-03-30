<?php
// ============================================================
//  FixIt KMUTNB — API Backend (XAMPP / MySQL)
//  แทนที่ AWS Lambda + DynamoDB + S3
// ============================================================

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ── Database Config ──────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'fixit_kmutnb');
define('DB_USER', 'root');
define('DB_PASS', '');   // XAMPP default = ว่าง

// ── Upload Config ────────────────────────────────────────────
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'http://localhost/fixit_xampp/uploads/');

// ── PDO connection ───────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}

// ── Helpers ──────────────────────────────────────────────────
function respond(int $code, mixed $data): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// แปลง row snake_case → camelCase ให้ตรงกับ JS frontend เดิม
function toCamel(array $row): array {
    return [
        'requestId'   => $row['request_id'],
        'userId'      => $row['user_id'],
        'status'      => $row['status'],
        'description' => $row['description'],
        'room'        => $row['room'],
        'imageUrl'    => $row['image_url'],
        'createdAt'   => $row['created_at'],
        'completedAt' => $row['completed_at'] ?? null,
    ];
}

// บันทึกรูป base64 → /uploads/ (แทน S3)
function saveImage(string $base64, string $requestId): string {
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    if (str_contains($base64, ',')) $base64 = explode(',', $base64, 2)[1];
    $binary = base64_decode($base64);
    if ($binary === false) return '';
    $ext = 'jpg';
    $sig = substr($binary, 0, 4);
    if (str_starts_with($sig, "\x89PNG")) $ext = 'png';
    elseif (str_starts_with($sig, 'GIF8')) $ext = 'gif';
    elseif (str_starts_with($sig, 'RIFF')) $ext = 'webp';
    $filename = $requestId . '.' . $ext;
    if (file_put_contents(UPLOAD_DIR . $filename, $binary) === false) return '';
    return UPLOAD_URL . $filename;
}

// ลบไฟล์รูป (แทน S3 DeleteObject)
function deleteImage(string $requestId): void {
    foreach (['jpg','png','gif','webp'] as $ext) {
        $p = UPLOAD_DIR . $requestId . '.' . $ext;
        if (file_exists($p)) @unlink($p);
    }
}

// ── Router ───────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

// GET — ดึงข้อมูลทั้งหมด
if ($method === 'GET') {
    $rows = getDB()
        ->query("SELECT * FROM maintenance_requests ORDER BY created_at DESC")
        ->fetchAll();
    respond(200, array_map('toCamel', $rows));
}

// POST — สร้างคำร้องใหม่
if ($method === 'POST') {
    $requestId = 'REQ-' . time();
    $userId    = trim($body['userId']      ?? 'ไม่ระบุ');
    $status    = trim($body['status']      ?? 'รอตรวจสอบ');
    $desc      = trim($body['description'] ?? 'ไม่มีรายละเอียด');
    $room      = trim($body['room']        ?? 'ไม่ระบุ');
    $imageUrl  = '';

    if (!empty($body['image'])) {
        $imageUrl = saveImage($body['image'], $requestId);
    }

    $stmt = getDB()->prepare(
        "INSERT INTO maintenance_requests
           (request_id, user_id, status, description, room, image_url, created_at)
         VALUES
           (:rid, :uid, :st, :desc, :room, :img, NOW())"
    );
    $stmt->execute([
        ':rid'  => $requestId,
        ':uid'  => $userId,
        ':st'   => $status,
        ':desc' => $desc,
        ':room' => $room,
        ':img'  => $imageUrl,
    ]);

    respond(201, [
        'message'   => 'Created successfully',
        'requestId' => $requestId,
        'imageUrl'  => $imageUrl,
    ]);
}

// PUT — อัปเดตสถานะ
if ($method === 'PUT') {
    $requestId = trim($body['requestId'] ?? '');
    $status    = trim($body['status']    ?? '');

    if (!$requestId || !$status) {
        respond(400, ['error' => 'Missing requestId or status']);
    }

    $sql = $status === 'เสร็จสิ้น'
         ? "UPDATE maintenance_requests SET status=:st, completed_at=NOW() WHERE request_id=:id"
         : "UPDATE maintenance_requests SET status=:st WHERE request_id=:id";

    $stmt = getDB()->prepare($sql);
    $stmt->execute([':st' => $status, ':id' => $requestId]);

    if ($stmt->rowCount() === 0) respond(404, ['error' => 'Request not found']);
    respond(200, ['message' => 'Status updated successfully']);
}

// DELETE — ลบคำร้อง
if ($method === 'DELETE') {
    $requestId = trim($body['requestId'] ?? '');
    if (!$requestId) respond(400, ['error' => 'Missing requestId']);

    deleteImage($requestId);   // ลบรูปก่อน (แทน S3)

    $stmt = getDB()->prepare("DELETE FROM maintenance_requests WHERE request_id=:id");
    $stmt->execute([':id' => $requestId]);

    if ($stmt->rowCount() === 0) respond(404, ['error' => 'Request not found']);
    respond(200, ['message' => 'Deleted successfully', 'requestId' => $requestId]);
}

respond(405, ['error' => 'Method not allowed']);
