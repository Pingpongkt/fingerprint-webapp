<?php
// เชื่อมต่อฐานข้อมูล
include 'db/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role']; // บทบาทที่เลือกจากฟอร์ม

    // เข้ารหัสรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // บันทึกข้อมูลลงในฐานข้อมูล
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $username, $hashed_password, $role);
    $stmt->execute();

    $_SESSION['message'] = 'ลงทะเบียนสำเร็จ! คุณสามารถเข้าสู่ระบบได้แล้ว';
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียน</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>ลงทะเบียน</h2>

            <form action="register.php" method="POST">
                <label for="username">ชื่อผู้ใช้:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">รหัสผ่าน:</label>
                <input type="password" id="password" name="password" required>

                <label for="role">บทบาท:</label>
                <select id="role" name="role" required>
                    <option value="user">student</option>
                    <option value="admin">admin</option>
                </select>

                <button type="submit">ลงทะเบียน</button>
            </form>

            <p>มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
        </div>
    </div>
</body>
</html>
