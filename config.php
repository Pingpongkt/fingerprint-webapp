<?php
// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$servername = "localhost";      // ชื่อเซิร์ฟเวอร์ (localhost หากใช้เครื่องเซิร์ฟเวอร์เดียวกัน)
$username = "root";             // ชื่อผู้ใช้ฐานข้อมูล
$password = "";                 // รหัสผ่าน (ใส่รหัสหากมี)
$dbname = "fingerprint_db";     // ชื่อฐานข้อมูล

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}
?>
