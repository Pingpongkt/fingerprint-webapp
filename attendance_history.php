<?php
session_start();
include 'db/config.php';

$course_id_search = $_GET['course_id'] ?? '';
$date_search = $_GET['date'] ?? '';

// ดึงข้อมูลวิชาจากฐานข้อมูล
$course_query = "SELECT course_id, course_name FROM courses";
$courses_result = $conn->query($course_query);

// สร้าง query สำหรับดึงข้อมูลการเข้าเรียน
$query = "SELECT a.attendance_time, c.course_name, f.student_name, f.student_id, a.attendance_status
          FROM attendance a 
          JOIN courses c ON a.course_id = c.course_id 
          JOIN fingerprints f ON a.finger_id = f.finger_id  
          WHERE 1";

if ($course_id_search) {
    $query .= " AND a.course_id = '$course_id_search'";
}
if ($date_search) {
    $query .= " AND DATE(a.attendance_time) = '$date_search'";
}
$query .= " ORDER BY a.attendance_time DESC";
$attendance_result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประวัติการเข้าเรียน</title>
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
            max-width: 1100px;
            margin: 30px auto;
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        form {
            margin-bottom: 20px;
        }
        select, input[type="date"], button {
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-right: 12px;
            font-family: 'Sarabun', sans-serif;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #45a049;
        }
        .export-button {
            background-color: #2196F3;
        }
        .export-button:hover {
            background-color: #0b7dda;
        }
        .back-button {
            background-color: #f44336;
            text-decoration: none;
            padding: 10px 20px;
            color: white;
            border-radius: 8px;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #e53935;
        }
        .button-container {
            margin-bottom: 20px;
        }
        .button-container button, .back-button {
            margin-right: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            background-color: #fff;
        }
        th, td {
            padding: 14px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 16px;
        }
        th {
            background-color: #2e7d32;
            color: white;
        }
        td {
            background-color: #f9f9f9;
        }
        tr:nth-child(even) td {
            background-color: #f1f1f1;
        }
        tr:hover td {
            background-color: #e1f5e1;
        }
        .search-form {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .search-form label {
            font-size: 16px;
        }
    </style>
</head>
<body>

<header>
    <img src="images/kmutnb_logo.png" alt="ตรามหาวิทยาลัย">
    <h1>ประวัติการเข้าเรียน</h1>
</header>

<div class="container">
    <!-- ปุ่มกลับหน้าหลักและส่งออก Excel ไว้ข้างๆ กัน -->
    <div class="button-container">
        <a href="index.php" class="back-button">← กลับหน้าหลัก</a>
        <form action="export_attendance_excel.php" method="GET" style="display: inline-block;">
            <input type="hidden" name="course_id" value="<?= htmlspecialchars($course_id_search) ?>">
            <input type="hidden" name="date" value="<?= htmlspecialchars($date_search) ?>">
            <button type="submit" class="export-button">📄 ส่งออก Excel</button>
        </form>
    </div>

    <!-- ฟอร์มค้นหา -->
    <form action="attendance_history.php" method="GET" class="search-form">
        <div>
            <label for="course_id">วิชา:</label>
            <select id="course_id" name="course_id">
                <option value="">เลือกวิชา</option>
                <?php while ($course = $courses_result->fetch_assoc()) { ?>
                    <option value="<?= $course['course_id'] ?>" <?= ($course['course_id'] == $course_id_search) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($course['course_name']) ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div>
            <label for="date">วันที่:</label>
            <input type="date" id="date" name="date" value="<?= htmlspecialchars($date_search) ?>">
        </div>

        <button type="submit">🔍 ค้นหา</button>
    </form>

    <!-- ตารางข้อมูลการเข้าเรียน -->
    <h2 style="margin-top: 30px; text-align: center; font-size: 24px; font-weight: bold;">📊 ข้อมูลการเข้าเรียน</h2>

    <table>
        <tr>
            <th>รหัสนักศึกษา</th>
            <th>ชื่อนักศึกษา</th>
            <th>วิชา</th>
            <th>วันที่และเวลา</th>
            <th>สถานะ</th>
        </tr>

        <?php
        if ($attendance_result && $attendance_result->num_rows > 0) {
            while ($row = $attendance_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['student_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                echo "<td>" . date('Y-m-d H:i:s', strtotime($row['attendance_time'])) . "</td>";
                echo "<td>" . htmlspecialchars($row['attendance_status']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>ไม่พบข้อมูล</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>
