<?php session_start(); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <div class="form-container">
            <h2>เข้าสู่ระบบ</h2>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <form action="login_action.php" method="POST">
                <label for="username">ชื่อผู้ใช้:</label>
                <input type="text" id="username" name="username" required><br>

                <label for="password">รหัสผ่าน:</label>
                <input type="password" id="password" name="password" required><br>

                <button type="submit">เข้าสู่ระบบ</button>
            </form>

            <p>ยังไม่มีบัญชี? <a href="register.php">ลงทะเบียน</a></p>
        </div>
    </div>
</body>
</html>
