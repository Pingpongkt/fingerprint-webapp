<?php
include '../db/config.php';

$finger_id = $_GET['finger_id'] ?? '';
$response = ['status' => 'free'];

if (!empty($finger_id)) {
    $stmt = $conn->prepare("SELECT student_id, student_name FROM fingerprints WHERE finger_id = ? LIMIT 1");
    $stmt->bind_param("i", $finger_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $response = [
            'status' => 'used',
            'student_id' => $row['student_id'],
            'student_name' => $row['student_name']
        ];
    }

    $stmt->close();
}

echo json_encode($response);
?>
