<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

session_start();
include 'db/config.php';

// ตรวจสอบว่าได้ส่งค่า 'course_id' จากฟอร์มหรือไม่
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : '';

// สร้าง query สำหรับดึงข้อมูลตามวิชาที่เลือก
$query = "SELECT a.attendance_time, c.course_name, f.student_name, f.student_id, a.attendance_status
          FROM attendance a 
          JOIN courses c ON a.course_id = c.course_id 
          JOIN fingerprints f ON a.finger_id = f.finger_id";

if ($course_id) {
    // กรองข้อมูลตามวิชา
    $query .= " WHERE a.course_id = " . intval($course_id);
}

// เปลี่ยนการเรียงข้อมูลเป็นจากแรกสุดไปหาสุดท้าย (ASC)
$query .= " ORDER BY a.attendance_time ASC";

$attendance_result = $conn->query($query);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// กำหนดชื่อหัวตาราง
$sheet->setCellValue('A1', 'รหัสนักศึกษา');
$sheet->setCellValue('B1', 'ชื่อนักศึกษา');
$sheet->setCellValue('C1', 'วิชา');
$sheet->setCellValue('D1', 'วันที่และเวลา');
$sheet->setCellValue('E1', 'สถานะการเข้าเรียน');

// สไตล์หัวตาราง
$sheet->getStyle('A1:E1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 14, 'name' => 'TH SarabunPSK'],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
               'startColor' => ['rgb' => 'D9E1F2']]
]);

$rowNumber = 2;
while ($row = $attendance_result->fetch_assoc()) {
    $sheet->setCellValueExplicit('A' . $rowNumber, $row['student_id'], DataType::TYPE_STRING);
    $sheet->setCellValue('B' . $rowNumber, $row['student_name']);
    $sheet->setCellValue('C' . $rowNumber, $row['course_name']);
    $sheet->setCellValue('D' . $rowNumber, date('Y-m-d H:i:s', strtotime($row['attendance_time'])));
    $sheet->setCellValue('E' . $rowNumber, $row['attendance_status']);

    // สไตล์ชื่อนักศึกษา
    $sheet->getStyle('B' . $rowNumber)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => '1F4E78'],
            'name' => 'TH SarabunPSK',
            'size' => 16
        ]
    ]);

    // สไตล์สถานะการเข้าเรียน
    $statusColor = $row['attendance_status'] === 'มาเรียน' ? '006400' : 'FF0000';
    $sheet->getStyle('E' . $rowNumber)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => $statusColor],
            'name' => 'TH SarabunPSK',
            'size' => 16
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER
        ]
    ]);

    $rowNumber++;
}

// ขยายความกว้างอัตโนมัติ
foreach (range('A', 'E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = 'attendance_report.xlsx';

// ส่งออกไฟล์ Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
