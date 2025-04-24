<?php
session_start();
include 'db/config.php';
date_default_timezone_set('Asia/Bangkok');

// ดึงรายวิชาทั้งหมด
$course_query = "SELECT course_id, course_name FROM courses";
$course_result = $conn->query($course_query);

$message = '';
$student_info = '';

// ตรวจสอบการส่งฟอร์มเลือกวิชา
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $course_id = intval($_POST['course_id']);
    $_SESSION['selected_course_id'] = $course_id;

    if ($course_id > 0) {
        $message = "✅ เลือกวิชาสำเร็จ กรุณาสแกนลายนิ้วมือเพื่อเช็คชื่อ";
    } else {
        $message = "⚠️ กรุณาเลือกวิชาก่อน";
    }
}

// ตรวจสอบการสแกนลายนิ้วมือ
if (isset($_GET['finger_id'])) {
    $finger_id = intval($_GET['finger_id']);

    // ใช้ course_id จาก session เท่านั้น
    if (isset($_SESSION['selected_course_id'])) {
        $course_id = $_SESSION['selected_course_id'];

        // เช็คข้อมูลลายนิ้วมือในวิชาที่เลือกเท่านั้น
        $query = "SELECT f.*, c.course_name 
                  FROM fingerprints f 
                  JOIN courses c ON f.course_id = c.course_id 
                  WHERE f.finger_id = ? AND f.course_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $finger_id, $course_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();

            // บันทึกการเช็คชื่อ
            $stmt = $conn->prepare("INSERT INTO attendance (finger_id, course_id, attendance_time, attendance_status) 
                                    VALUES (?, ?, NOW(), 'เข้าเรียน')");
            $stmt->bind_param("ii", $finger_id, $course_id);
            $stmt->execute();

            $student_info = "✅ เช็คชื่อสำเร็จ:<br>
                👤 ชื่อ: {$student['student_name']}<br>
                🆔 รหัส: {$student['student_id']}<br>
                📘 วิชา: {$student['course_name']}<br>
                ⏰ เวลา: " . date("d/m/Y H:i:s");

            // รีเซ็ตตัวเลือกวิชาเฉพาะ ไม่ทำลาย session ทั้งหมด
            unset($_SESSION['selected_course_id']);
            header("Refresh: 3; url=attendance_check.php");
        } else {
            $student_info = "❌ ไม่พบข้อมูลของลายนิ้วมือนี้ในวิชานี้";
        }
    } else {
        $student_info = "⚠️ กรุณาเลือกวิชาก่อนที่จะสแกนลายนิ้วมือ";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เช็คชื่อรายวิชา</title>
    <!-- Apply Sarabun font -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet">
    <style>
       body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }
        header {
            background: linear-gradient(90deg, #43cea2, #185a9d);
            color: white;
            padding: 20px 50px;
            display: flex;
            justify-content: center; /* ใช้ center เพื่อจัดตำแหน่งโลโก้และข้อความในแนวนอน */
            align-items: center; /* ใช้ center เพื่อจัดตำแหน่งในแนวตั้ง */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center; /* เพิ่มการจัดตำแหน่งข้อความให้กึ่งกลาง */
            height: 150px; /* กำหนดความสูงของ header */
        }

        header img {
            height: 120px; /* ปรับขนาดโลโก้ให้ใหญ่ขึ้น */
            margin-right: 20px; /* เพิ่มระยะห่างระหว่างโลโก้และข้อความ */
        }

        header h1 {
            margin: 0;
            font-family: 'Sarabun', sans-serif; /* ใช้ฟอนต์ Sarabun สำหรับ h1 */
            font-size: 40px; /* ทำให้ข้อความใหญ่ขึ้น */
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase; /* ทำให้ข้อความเป็นตัวพิมพ์ใหญ่ทั้งหมด */
            line-height: 1.2; /* เพิ่มระยะห่างระหว่างบรรทัด */
        }

        .container {
            background-color: #ffffff;
            padding: 40px;
            max-width: 600px;
            margin: 20px auto;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            text-align: center;
            font-size: 32px;
            color: #333;
        }

        .message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            font-size: 16px;
        }

        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }

        select, button {
            padding: 12px;
            margin-top: 20px;
            width: 100%;
            border-radius: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            background-color: #fafafa;
            transition: all 0.3s ease;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
        }

        button:hover {
            background-color: #45a049;
        }

        .back-button {
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
            margin-top: 30px;
            text-align: center;
        }

        .back-button:hover {
            background-color: #e53935;
        }

        select:focus, button:focus {
            outline: none;
            box-shadow: 0 0 5px rgba(72, 133, 237, 0.5);
        }
    </style>
</head>

<body>

<header>
    <img src="images/kmutnb_logo.png" alt="ตรามหาวิทยาลัย">
    <h1>ระบบเช็คชื่อเข้าเรียน</h1>
</header>

<div class="container">
    <h2>📚 ระบบเช็คชื่อเข้าเรียน</h2>

    <?php if (!empty($message)): ?>
        <div class="message <?php echo (strpos($message, '✅') !== false) ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($student_info)): ?>
        <div class="message success">
            <?php echo $student_info; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label for="course_id">เลือกวิชา:</label>
        <select name="course_id" id="course_id" required>
            <option value="">-- กรุณาเลือกวิชา --</option>
            <?php while ($course = $course_result->fetch_assoc()): ?>
                <option value="<?php echo $course['course_id']; ?>" 
                    <?php echo (isset($_SESSION['selected_course_id']) && $_SESSION['selected_course_id'] == $course['course_id']) ? 'selected' : ''; ?>>
                    <?php echo $course['course_name']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">📘 ยืนยันการเลือกวิชา</button>
        <button type="button" onclick="startScan()">📲 เริ่มสแกนลายนิ้วมือ</button>
    </form>

    <a href="index.php" class="back-button">← กลับหน้าหลัก</a>
</div>

<script>
    function startScan() {
        const courseId = document.getElementById('course_id').value;

        if (!courseId) {
            alert("⚠️ กรุณาเลือกวิชาก่อน");
            return;
        }

        fetch(`http://172.20.10.9/attendance_check?course_id=${courseId}`)
            .then(res => res.json())
            .then(data => {
                console.log("DEBUG: data from ESP32", data);
                if (data.status === 'success') {
                    location.href = `attendance_check.php?finger_id=${data.finger_id}`;
                } else {
                    alert("❌ " + data.message);
                }
            })
            .catch(() => alert("❌ ไม่สามารถเชื่อมต่อกับ ESP32"));
    }
</script>

</body>
</html>
