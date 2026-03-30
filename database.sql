-- ==============================
-- FixIt KMUTNB - Database Setup
-- ใช้กับ XAMPP (phpMyAdmin)
-- ==============================

CREATE DATABASE IF NOT EXISTS fixit_kmutnb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE fixit_kmutnb;

CREATE TABLE IF NOT EXISTS maintenance_requests (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    request_id  VARCHAR(50)  NOT NULL UNIQUE,
    user_id     VARCHAR(255) NOT NULL DEFAULT 'ไม่ระบุ',
    status      VARCHAR(50)  NOT NULL DEFAULT 'รอตรวจสอบ',
    description TEXT         NOT NULL,
    room        VARCHAR(255) NOT NULL DEFAULT 'ไม่ระบุ',
    image_url   VARCHAR(500) DEFAULT '',
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME    DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตัวอย่างข้อมูล (optional)
-- INSERT INTO maintenance_requests (request_id, user_id, status, description, room)
-- VALUES ('REQ-1234567890', 'นายทดสอบ ระบบ', 'รอตรวจสอบ', 'ไฟทางเดินดับ', '81-89 - อาคารคณะวิศวกรรมศาสตร์');
