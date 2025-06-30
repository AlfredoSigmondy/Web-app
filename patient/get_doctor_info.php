<?php
if (isset($_GET['doctor_id'])) {
    include_once __DIR__ . '/../database/conection_db.php';
    try {
        $doctorId = (int)$_GET['doctor_id']; // Ensure integer type
        $stmt = $conn->prepare("SELECT Username, Email, ContactNumber, Specialization 
                               FROM doctors 
                               WHERE MedicID = ? AND Status = 'Approved'");
        $stmt->bind_param("i", $doctorId); // Use "i" for integer
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $doctor = $result->fetch_assoc();
            header('Content-Type: application/json');
            echo json_encode([
                'Username' => $doctor['Username'],
                'Email' => $doctor['Email'],
                'ContactNumber' => $doctor['ContactNumber'],
                'Specialization' => $doctor['Specialization']
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Approved doctor not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
    
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Doctor ID required']);
}
?>