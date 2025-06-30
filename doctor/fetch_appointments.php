
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../database/conection_db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Use POST.']);
    exit;
}

$doctorID = $_POST['doctor_id'] ?? null;
$appointmentDateTime = $_POST['selected_date'] ?? null;
$notes = $_POST['notes'] ?? '';
$patientName = $_POST['patient_name'] ?? '';

if (!$doctorID || !$appointmentDateTime || !$patientName) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    $status = 'pending';
    $created_at = date('Y-m-d H:i:s');
    $updated_at = $created_at;

    // Insert appointment (patient_id is NULL, but patient_name is stored in notes)
    $notesWithName = "Patient: $patientName\n$notes";
    $stmt = $conn->prepare("INSERT INTO appointments (doctor_id, patient_id, appointment_date, notes, status, created_at, updated_at) VALUES (?, NULL, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $doctorID, $appointmentDateTime, $notesWithName, $status, $created_at, $updated_at);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Appointment booked successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>