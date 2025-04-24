<?php
include 'db/config.php';

if (isset($_GET['finger_id']) && is_numeric($_GET['finger_id'])) {
    $finger_id = $_GET['finger_id'];

    // ลบข้อมูลจากฐานข้อมูล
    $stmt = $conn->prepare("DELETE FROM fingerprints WHERE finger_id = ?");
    $stmt->bind_param("i", $finger_id);

    if ($stmt->execute()) {
        // ลบเสร็จเรียบร้อยแล้ว รีเฟรชหน้า
        header("Location: index.php"); // หรือหน้าอื่นที่ต้องการ
        exit;  // เพื่อให้การส่งคำสั่งหยุดที่ตรงนี้
    } else {
        // ถ้ามีข้อผิดพลาด
        echo "เกิดข้อผิดพลาด: " . $conn->error;
    }
} else {
    // หากไม่มีหรือเป็นค่าที่ไม่ถูกต้อง
    echo "ไม่พบข้อมูลลายนิ้วมือที่ต้องการลบ";
}
?>
