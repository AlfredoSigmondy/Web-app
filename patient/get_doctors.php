<?php
if (isset($_GET['specialization'])) {
    include_once __DIR__ . '/../database/conection_db.php';
    try {
        $specialization = $_GET['specialization'];
        $stmt = $conn->prepare("SELECT MedicID, Username 
                               FROM doctors 
                               WHERE Specialization = ? AND Status = 'Approved'");
        $stmt->bind_param("s", $specialization);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $doctors = [];
        while ($row = $result->fetch_assoc()) {
            $doctors[] = [
                'MedicID' => (int)$row['MedicID'], // Ensure ID is integer
                'Username' => $row['Username']
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($doctors);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Specialization required']);
}
?>