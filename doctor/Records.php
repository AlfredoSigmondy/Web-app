<?php
session_start();
include_once __DIR__ . '/../database/conection_db.php';
$doctor_id = $_SESSION['MedicID'] ?? null;

// Fetch past appointments (status = 'Done')
$appointments = [];
if ($doctor_id) {
    $sql = "SELECT a.id AS AppointmentID, a.appointment_date, p.Username AS PatientName
            FROM appointments a
            JOIN patient p ON a.patient_id = p.PatientID
            WHERE a.doctor_id = ? AND a.status = 'Done'
            ORDER BY a.appointment_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Records - eMedConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; }
        .records-tab {
            border: none;
            background: #f3f3f3;
            border-radius: 24px;
            padding: 6px 12px;
            font-weight: 500;
            color: #888;
            margin-right: 8px;
            cursor: pointer;
        }
        .records-tab.active {
            background: #2eb872;
            color: #fff;
        }
        .record-card {
            background: #fff;
            border-radius: 24px;
            padding: 20px 32px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 12px #0001;
            font-size: 1.1rem;
        }
        .record-arrow {
            color: #2eb872;
            font-size: 1.5rem;
        }
        .records-title {
            font-weight: 700;
            color: #14532d;
            margin-bottom: 28px;
            margin-top: 18px;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include_once __DIR__ . '/../SideBar/DSidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <div class="records-title">Medical records</div>
        <div class="mb-4">
            <button class="records-tab active">Past Appointments</button>
        </div>
        <?php if (count($appointments) > 0): ?>
            <?php foreach ($appointments as $appt): ?>
                <div class="record-card">
                    <div>
                        <div><b><?= htmlspecialchars($appt['PatientName']) ?></b></div>
                        <div style="font-size:0.98rem;color:#888;">
                            <?= htmlspecialchars(date('F d, Y', strtotime($appt['appointment_date']))) ?>
                        </div>
                    </div>
                    <div>
                        <i class="bi bi-chevron-right record-arrow"></i>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-muted">No past appointments found.</div>
        <?php endif; ?>
    </div>
    <?php include_once __DIR__ . '/../Sidebar_Right/SidebarRight.php'; ?>
</div>
</body>
</html>