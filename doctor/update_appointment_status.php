<?php
require_once __DIR__ . '/../database/conection_db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Validate input parameters
$appointment_id = $_POST['appointment_id'] ?? null;
$action = $_POST['action'] ?? null;
$refer_doctor_id = $_POST['refer_doctor_id'] ?? null;

if (!$appointment_id || !$action) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing appointment_id or action']);
    exit;
}

if (!in_array($action, ['accept', 'refer', 'reject'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
    exit;
}

if ($action === 'refer' && !$refer_doctor_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing refer_doctor_id for referral']);
    exit;
}

try {
    if ($action === 'accept') {
        // Mark as accepted
        $status = 'Scheduled';
        $stmt = $conn->prepare("UPDATE appointments SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $status, $appointment_id);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            $stmt->close();
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Appointment not found']);
            exit;
        }
        $stmt->close();

        // Get patient email
        $stmt = $conn->prepare("SELECT patient_id, appointment_date FROM appointments WHERE id = ?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $stmt->bind_result($patient_id, $appointment_date);
        $stmt->fetch();
        $stmt->close();

        if ($patient_id) {
            $stmt = $conn->prepare("SELECT email, Username FROM patients WHERE id = ?");
            $stmt->bind_param("i", $patient_id);
            $stmt->execute();
            $stmt->bind_result($email, $patient_name);
            $stmt->fetch();
            $stmt->close();

            if ($email) {
                $subject = "Your Appointment Has Been Accepted";
                $message = "Dear $patient_name,\n\nYour appointment on $appointment_date has been accepted.\n\nThank you!";
                $headers = "From: no-reply@emedconsultation.com\r\n";
                mail($email, $subject, $message, $headers);
            }
        }

        echo json_encode(['success' => true]);
    } elseif ($action === 'refer') {
        // Validate refer_doctor_id
        $stmt = $conn->prepare("SELECT Username FROM doctors WHERE MedicID = ?");
        $stmt->bind_param("i", $refer_doctor_id);
        $stmt->execute();
        $stmt->bind_result($refer_doctor_name);
        if (!$stmt->fetch()) {
            $stmt->close();
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid refer_doctor_id']);
            exit;
        }
        $stmt->close();

        // Update appointment with referral
        $status = 'referred';
        $note = "Referred to Dr. $refer_doctor_name (ID: $refer_doctor_id)";
        $stmt = $conn->prepare("UPDATE appointments SET status = ?, updated_at = NOW(), notes = CONCAT(COALESCE(notes, ''), '\n', ?) WHERE id = ?");
        $stmt->bind_param("ssi", $status, $note, $appointment_id);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            $stmt->close();
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Appointment not found']);
            exit;
        }
        $stmt->close();
        echo json_encode(['success' => true]);
    } elseif ($action === 'reject') {
        // Check if already approved
        $stmt = $conn->prepare("SELECT status FROM appointments WHERE id = ?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $stmt->bind_result($current_status);
        if (!$stmt->fetch()) {
            $stmt->close();
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Appointment not found']);
            exit;
        }
        $stmt->close();

        if ($current_status === 'approved') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cannot cancel an approved appointment']);
            exit;
        }

        // Delete the appointment
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            $stmt->close();
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Appointment not found']);
            exit;
        }
        $stmt->close();
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>