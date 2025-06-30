<?php
require_once __DIR__ . '/../database/conection_db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$doctor_id = $_GET['doctor_id'] ?? null;
$patient_name = $_GET['patient_name'] ?? null;

$where = [];
$params = [];
$types = '';

if ($doctor_id) {
    $where[] = 'doctor_id = ?';
    $params[] = $doctor_id;
    $types .= 'i';
}
if ($patient_name) {
    $where[] = 'notes LIKE ?';
    $params[] = "%$patient_name%";
    $types .= 's';
}

$sql = "SELECT * FROM appointments";
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY appointment_date DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

echo json_encode(['success' => true, 'appointments' => $appointments]);
?>