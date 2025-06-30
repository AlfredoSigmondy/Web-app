<?php
session_start();
require_once __DIR__ . '/../database/conection_db.php';

header('Content-Type: application/json');

// Get the logged-in patient ID
$patient_id = $_SESSION['user_id'] ?? null;
if (!$patient_id) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Fetch accepted appointments for this patient, join with doctor info
$stmt = $conn->prepare(
    "SELECT a.id, a.appointment_date, a.status, a.notes, d.Username AS doctor_name
     FROM appointments a
     JOIN doctors d ON a.doctor_id = d.MedicID
     WHERE a.patient_id = ? AND (a.status = 'approved' OR a.status = 'Scheduled')
     ORDER BY a.appointment_date DESC"
);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
echo json_encode(['success' => true, 'appointments' => $appointments]);
?>