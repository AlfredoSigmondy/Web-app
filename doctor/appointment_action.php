<?php
session_start();
include_once __DIR__ . '/../database/conection_db.php';

if (!isset($_SESSION['doctor_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (isset($_POST['appointment_id']) && isset($_POST['action'])) {
    try {
        $appointment_id = (int)$_POST['appointment_id'];
        $action = $_POST['action'];
        $doctor_id = (int)$_SESSION['doctor_id'];

        // Validate action
        $valid_actions = ['accept', 'reject', 'refer'];
        if (!in_array($action, $valid_actions)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            exit;
        }

        // Map action to status
        $status = $action === 'accept' ? 'Completed' : ($action === 'reject' ? 'Cancelled' : 'No-Show');

        $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?");
        $stmt->bind_param("sii", $status, $appointment_id, $doctor_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'status' => $status]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Appointment not found or not authorized']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
}
?>