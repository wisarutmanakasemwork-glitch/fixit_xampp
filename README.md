# FixIt KMUTNB — XAMPP Version

## วิธีติดตั้ง

### 1. วางโฟลเดอร์ใน XAMPP
คัดลอกโฟลเดอร์ `fixit_xampp` ทั้งหมดไปไว้ที่:
```
C:\xampp\htdocs\fixit_xampp\
```

### 2. สร้างฐานข้อมูล
- เปิด XAMPP → กด Start ที่ Apache และ MySQL
- เปิดเบราว์เซอร์ไปที่ http://localhost/phpmyadmin
- คลิก "Import" แล้วเลือกไฟล์ `setup_database.sql`
- หรือคัดลอก SQL ใน `setup_database.sql` แล้ว paste ใน "SQL" tab

### 3. เปิดเว็บ
ไปที่ http://localhost/fixit_xampp/

## โครงสร้างไฟล์
```
fixit_xampp/
├── index.html          ← หน้าเว็บหลัก (frontend เดิม)
├── api.php             ← Backend PHP (แทน AWS Lambda)
├── setup_database.sql  ← สร้าง Database + Table
├── style.css           ← CSS เดิม
├── FixItKMUTNB logo.png
└── uploads/            ← เก็บรูปที่อัปโหลด (แทน S3)
    └── .htaccess       ← ป้องกัน execute PHP ใน folder
```

## การแทนที่ AWS
| AWS เดิม | XAMPP ใหม่ |
|----------|-----------|
| DynamoDB | MySQL (fixit_kmutnb) |
| S3 Bucket | /uploads/ folder |
| Lambda Function | api.php |
| API Gateway | http://localhost/fixit_xampp/api.php |
