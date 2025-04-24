<?php
session_start();
include 'db/config.php'; // เชื่อมต่อกับฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // ตรวจสอบการกรอกข้อมูล
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
        header('Location: login.php');
        exit();
    }

    // ตรวจสอบข้อมูลผู้ใช้ในฐานข้อมูล
    $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            session_regenerate_id(true); // เปลี่ยน session ID ใหม่เพื่อความปลอดภัย

            if ($_SESSION['role'] === 'admin') {
                header('Location: index.php'); // หากเป็น admin รีไดเร็กต์ไปที่หน้าแรก
            } else {
                header('Location: index.php'); // หากเป็นผู้ใช้ทั่วไป รีไดเร็กต์ไปที่หน้าแรก
            }
            exit();
        } else {
            $_SESSION['error_message'] = "รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $_SESSION['error_message'] = "ชื่อผู้ใช้ไม่ถูกต้อง";
    }

    header('Location: login.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}
?>
