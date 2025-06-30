<?php
include_once __DIR__ . '/../database/conection_db.php';

// Validate input
$required = ['doctor_id', 'patient_name', 'appointment_time'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        die(json_encode(['error' => "Missing required field: $field"]));
    }
}

try {
    $doctorId = (int)$_POST['doctor_id'];
    $patientName = $_POST['patient_name'];
    $appointmentTime = $_POST['appointment_time'];
    
    // Parse date and time
    $dateTime = new DateTime($appointmentTime);
    $date = $dateTime->format('Y-m-d');
    $time = $dateTime->format('H:i:s');
    
    // Check if slot is still available
    $checkStmt = $conn->prepare("
        SELECT 1 FROM appointments 
        WHERE doctor_id = ? 
        AND AppointmentDate = ? 
        AND StartTime = ?
        AND Status IN ('Scheduled', 'Completed')
    ");
    $checkStmt->bind_param("iss", $doctorId, $date, $time);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows > 0) {
        http_response_code(409);
        die(json_encode(['error' => 'This time slot is no longer available']));
    }
    
    // Get patient ID from session (you'll need to implement this)
    session_start();
    $patientId = $_SESSION['patient_id'] ?? null;
    if (!$patientId) {
        http_response_code(401);
        die(json_encode(['error' => 'Patient not authenticated']));
    }
    
    // Create appointment
    $insertStmt = $conn->prepare("
        INSERT INTO appointments 
        (doctor_id, patient_id, AppointmentDate, StartTime, EndTime, Status) 
        VALUES (?, ?, ?, ?, ?, 'Scheduled')
    ");
    
    // Calculate end time (30 minutes after start)
    $endTime = (clone $dateTime)->modify('+30 minutes')->format('H:i:s');
    
    $insertStmt->bind_param("iisss", $doctorId, $patientId, $date, $time, $endTime);
    $insertStmt->execute();
    
    echo json_encode([
        'success' => true,
        'appointment_id' => $conn->insert_id,
        'date' => $date,
        'time' => $time
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}