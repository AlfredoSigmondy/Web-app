<?php
include_once __DIR__ . '/../database/conection_db.php';

header('Content-Type: application/json');

try {
    if (isset($_GET['specialization'])) {
        $specialization = $_GET['specialization'];
        $stmt = $conn->prepare("SELECT MedicID, Username FROM doctors WHERE Specialization = ? AND Status = 'Approved'");
        $stmt->bind_param("s", $specialization);
    } else {
        // No specialization: get all approved doctors
        $stmt = $conn->prepare("SELECT MedicID, Username FROM doctors WHERE Status = 'Approved'");
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = [
            'id' => (int)$row['MedicID'],
            'name' => $row['Username']
        ];
    }

    echo json_encode(['success' => true, 'doctors' => $doctors]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>