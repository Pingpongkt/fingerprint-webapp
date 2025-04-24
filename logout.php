<?php
session_start();
session_unset();  // ลบข้อมูลทั้งหมดใน session
session_destroy();  // ทำลาย session
header('Location: login.php');  // รีไดเร็กต์ไปที่หน้า login
exit();
?>
