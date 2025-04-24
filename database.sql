-- 1. สร้างฐานข้อมูล (หากยังไม่มี)
CREATE DATABASE IF NOT EXISTS user_system;

-- 2. เลือกใช้งานฐานข้อมูล
USE user_system;

-- 3. สร้างตารางผู้ใช้ (users)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,   -- กำหนด user_id เป็น Primary Key และให้เพิ่มอัตโนมัติ
    username VARCHAR(100) NOT NULL UNIQUE,     -- ชื่อผู้ใช้ต้องไม่ซ้ำ
    password VARCHAR(255) NOT NULL,            -- รหัสผ่านที่เก็บในรูปแบบที่แฮชแล้ว
    role VARCHAR(50) DEFAULT 'user',           -- ระบุตำแหน่งผู้ใช้ (เช่น admin, user)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- เก็บวันที่เวลาที่ผู้ใช้ถูกสร้าง
);

-- 4. เพิ่มข้อมูลผู้ใช้ตัวอย่าง (คุณสามารถเพิ่มหรือลบได้)
-- *** หมายเหตุ: ให้คุณใช้รหัสผ่านแฮชผ่าน PHP ก่อนเพิ่มลงในฐานข้อมูล ***
INSERT INTO users (username, password, role) VALUES 
('admin', 'password123', 'admin'),
('user1', 'password123', 'user');
