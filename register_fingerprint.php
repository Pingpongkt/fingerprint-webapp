<?php
session_start();
include 'db/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_name = $_POST['student_name'] ?? '';
    $student_id = $_POST['student_id'] ?? '';
    $course_id = $_POST['course'] ?? '';
    $finger_id = $_POST['finger_id'] ?? '';

    if (!empty($student_name) && !empty($student_id) && !empty($course_id) && !empty($finger_id)) {
        $check_stmt = $conn->prepare("SELECT * FROM fingerprints WHERE finger_id = ? AND course_id = ?");
        $check_stmt->bind_param("ii", $finger_id, $course_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error_message = "❌ ลายนิ้วมือนี้ถูกลงทะเบียนแล้วในวิชานี้!";
        } else {
            $stmt = $conn->prepare("INSERT INTO fingerprints (student_id, student_name, finger_id, status, course_id, timestamp) VALUES (?, ?, ?, 'pending', ?, NOW())");
            $stmt->bind_param("ssii", $student_id, $student_name, $finger_id, $course_id);
            if ($stmt->execute()) {
                $success_message = "✅ ลงทะเบียนลายนิ้วมือสำเร็จ!";
            } else {
                $error_message = "เกิดข้อผิดพลาด: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $error_message = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    }
}

$course_query = "SELECT course_id, course_name FROM courses";
$course_result = $conn->query($course_query);

$fingerprint_query = "SELECT f.*, c.course_name FROM fingerprints f LEFT JOIN courses c ON f.course_id = c.course_id ORDER BY f.timestamp DESC";
$fingerprint_result = $conn->query($fingerprint_query);
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ระบบลงทะเบียนลายนิ้วมือ</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Update font import -->
  <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet">
  <style>
    /* Apply the Sarabun font to the whole page */
    body {
      font-family: 'Sarabun', sans-serif;
      background-color: #f3f4f8;
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
      max-width: 900px;
      margin: 20px auto;
      background-color: white;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 25px;
      font-weight: 600;
    }

    label {
      font-size: 16px;
      font-weight: 500;
      color: #555;
      margin-top: 15px;
      display: block;
    }

    input[type="text"],
    input[type="number"],
    select {
      width: 100%;
      padding: 15px;
      margin-top: 10px;
      border: 2px solid #ddd;
      border-radius: 8px;
      font-size: 16px;
      transition: all 0.3s ease-in-out;
      box-sizing: border-box;
    }

    input:focus,
    select:focus {
      border-color: #007bff;
      outline: none;
      box-shadow: 0 0 10px rgba(0, 123, 255, 0.2);
    }

    button {
      width: 100%;
      padding: 15px;
      border: none;
      font-size: 18px;
      border-radius: 8px;
      margin-top: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease;
      color: white;
      background-color: #4CAF50;
    }

    button:hover {
      background-color: #4CAF50;
    }

    .result,
    .error {
      font-weight: bold;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 15px;
      text-align: center;
    }

    .result {
      background-color: #d4edda;
      color: #155724;
    }

    .error {
      background-color: #f8d7da;
      color: #721c24;
    }

    table {
      width: 100%;
      margin-top: 30px;
      border-collapse: collapse;
    }

    th {
      background-color: #007bff;
      color: white;
      padding: 15px;
      font-weight: 600;
      text-align: center;
    }

    td {
      padding: 15px;
      text-align: center;
      border-bottom: 1px solid #f1f1f1;
      font-size: 14px;
      color: #555;
    }

    tr:nth-child(even) {
      background-color: #f8f9fa;
    }

    .delete-btn {
      background-color: #ffc107;
      padding: 8px 16px;
      border-radius: 8px;
      color: white;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .delete-btn:hover {
      background-color:  #ffc107;
    }

    #scanResult {
      font-size: 16px;
      font-weight: 500;
      color: #333;
      margin-top: 15px;
    }

    .back-button {
      background-color: #f44336;
      padding: 12px;
      border-radius: 8px;
      color: white;
      text-align: center;
      cursor: pointer;
      transition: background-color 0.3s ease;
      width: 100%;
      font-size: 16px;
      margin-top: 20px;
    }

    .back-button:hover {
      background-color: #f44336;
    }
  </style>
</head>

<body>

<header>
    <img src="images/kmutnb_logo.png" alt="ตรามหาวิทยาลัย">
    <h1>ระบบลงทะเบียนลายนิ้วมือ</h1>
</header>

<div class="container">
  <?php if (isset($success_message)) echo "<p class='result'>$success_message</p>"; ?>
  <?php if (isset($error_message)) echo "<p class='error'>$error_message</p>"; ?>

  <form method="POST" id="registerForm">
    <label>ชื่อนักศึกษา:</label>
    <input type="text" name="student_name" required>

    <label>รหัสนักศึกษา:</label>
    <input type="text" name="student_id" required>

    <label>เลือกวิชา:</label>
    <select name="course" required>
      <option value="">-- เลือกวิชา --</option>
      <?php
      if ($course_result->num_rows > 0) {
          while ($row = $course_result->fetch_assoc()) {
              echo "<option value='{$row['course_id']}'>{$row['course_name']}</option>";
          }
      } else {
          echo "<option value=''>ไม่มีวิชา</option>";
      }
      ?>
    </select>

    <label>ID ลายนิ้วมือ:</label>
    <input type="number" name="finger_id" id="finger_id_input" required min="1" max="127" placeholder="กรุณากรอกหมายเลข ID (1-127)">

    <button type="button" class="scan-btn" onclick="startScan()">เริ่มสแกนลายนิ้วมือ</button>
    <div id="scanResult"></div>

    <button type="submit" id="submitBtn" class="save-btn" disabled>บันทึกข้อมูล</button>

    <button type="button" class="back-button" onclick="window.location.href='index.php'">
    ← กลับหน้าหลัก
</button>

    </form>
  </form>

  <h2>ข้อมูลที่ลงทะเบียนล่าสุด</h2>
  <table>
    <tr>
      <th>รหัสนักศึกษา</th>
      <th>ชื่อนักศึกษา</th>
      <th>ID ลายนิ้วมือ</th>
      <th>วิชา</th>
      <th>วันที่ลงทะเบียน</th>
      <th>จัดการ</th>
    </tr>
    <?php
    if ($fingerprint_result->num_rows > 0) {
        while ($row = $fingerprint_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['student_id']}</td>";
            echo "<td>{$row['student_name']}</td>";
            echo "<td>{$row['finger_id']}</td>";
            echo "<td>{$row['course_name']}</td>";
            echo "<td>" . date("d/m/Y H:i:s", strtotime($row['timestamp'])) . "</td>";
            echo "<td><button class='delete-btn' onclick='confirmDelete({$row['finger_id']})'>ลบ</button></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>ไม่มีข้อมูล</td></tr>";
    }
    ?>
  </table>
</div>

<script>
  function startScan() {
    const fingerId = document.getElementById("finger_id_input").value;
    const studentId = document.querySelector("input[name='student_id']").value;
    const submitBtn = document.getElementById("submitBtn");
    const scanResult = document.getElementById("scanResult");

    if (!fingerId || fingerId < 1 || fingerId > 127 || !studentId) {
      alert("กรุณากรอก ID ลายนิ้วมือ (1-127) และรหัสนักศึกษา");
      return;
    }

    scanResult.innerHTML = "⏳ กำลังตรวจสอบ...";
    submitBtn.disabled = true;

    fetch(api/check_finger.php?finger_id=${fingerId})
      .then(res => res.json())
      .then(data => {
        if (data.status === 'used') {
          if (data.student_id === studentId) {
            scanResult.innerHTML = ✅ ลายนิ้วมือเคยใช้โดยนักศึกษาเดิม: ${data.student_name}; 
            document.getElementById("finger_id_input").readOnly = true;
            submitBtn.disabled = false;
          } else {
            alert(❌ ลายนิ้วมือนี้ถูกใช้แล้วโดยนักศึกษาอื่น: ${data.student_name});
            scanResult.innerHTML = "ลายนิ้วมือนี้ถูกใช้โดยผู้อื่น";
          }
        } else {
          fetch(http://172.20.10.9/register_fingerprint?id=${fingerId})
            .then(res => res.json())
            .then(data => {
              if (data.status === 'success') {
                scanResult.innerHTML = ✅ ลงทะเบียนสำเร็จ! ID ลายนิ้วมือ: ${data.finger_id};
                document.getElementById("finger_id_input").readOnly = true;
                submitBtn.disabled = false;
              } else {
                scanResult.innerHTML = ❌ ล้มเหลว: ${data.message || 'กรุณาลองใหม่'};
              }
            })
            .catch(() => {
              alert("❌ ไม่สามารถเชื่อมต่อกับ ESP32 ได้");
              scanResult.innerHTML = "เชื่อมต่อไม่ได้";
            });
        }
      });
  }

  function confirmDelete(fingerId) {
    if (confirm("คุณแน่ใจว่าจะลบข้อมูลนี้?")) {
        window.location.href = delete_fingerprint.php?finger_id=${fingerId};
    }
  }
</script>

</body>
</html>