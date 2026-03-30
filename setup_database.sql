-- ============================================================
--  FixIt KMUTNB — Database Setup Script
--  รันไฟล์นี้ใน phpMyAdmin หรือ MySQL CLI ครั้งเดียว
-- ============================================================

-- 1. สร้าง Database
CREATE DATABASE IF NOT EXISTS fixit_kmutnb
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE fixit_kmutnb;

-- 2. สร้าง Table  (แทนที่ DynamoDB "MaintenanceRequests")
CREATE TABLE IF NOT EXISTS maintenance_requests (
    request_id   VARCHAR(50)   NOT NULL PRIMARY KEY,   -- เช่น REQ-1234567890
    user_id      VARCHAR(100)  NOT NULL DEFAULT 'ไม่ระบุ',
    status       VARCHAR(50)   NOT NULL DEFAULT 'รอตรวจสอบ',
    description  TEXT          NOT NULL,
    room         VARCHAR(200)  NOT NULL DEFAULT 'ไม่ระบุ',
    image_url    VARCHAR(500)  DEFAULT '',              -- path ไฟล์ใน /uploads/
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME      DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. ข้อมูลตัวอย่าง (ไม่บังคับ — ลบออกได้)
INSERT INTO maintenance_requests (request_id, user_id, status, description, room, created_at) VALUES
  ('REQ-DEMO001', 'นายทดสอบ ระบบ', 'รอตรวจสอบ',   'ไฟฟ้าดับทั้งชั้น',  '81 - อาคารคณะวิศวกรรมศาสตร์', NOW()),
  ('REQ-DEMO002', 'นางสาว ตัวอย่าง', 'กำลังดำเนินการ', 'ท่อน้ำรั่วในห้องน้ำ', '23 - อาคารนวมินทรราชินี', NOW()),
  ('REQ-DEMO003', 'นาย ผู้ใช้จริง', 'เสร็จสิ้น',   'เครื่องปรับอากาศเสีย', '77 - อาคาร 40 ปี มจพ.', NOW());
