<?php
// รวมไฟล์ที่จำเป็นจาก PhpSpreadsheet
require 'Fingerint/PhpSpreadsheet/src/PhpSpreadsheet/Spreadsheet.php';
require 'Fingerint/PhpSpreadsheet/src/PhpSpreadsheet/Writer/Xlsx.php';


// ใช้ namespace
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// สร้าง Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Hello World !');

// สร้าง Writer และเขียนไฟล์ Excel
$writer = new Xlsx($spreadsheet);
$writer->save('attendance.xlsx');
