<?php
// ...existing includes...
// Example: Fetch records from your database
$records = []; // Replace with your DB fetch, e.g. $records = getPatientRecords($conn, $patient_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="Records.css">
</head>
<body>
<div class="d-flex" style="min-height: 100vh;">
    <?php include_once __DIR__ . '/../SideBar/Sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
        <h4 class="fw-bold mt-4 mb-3">Medical records</h4>
        <div class="records-tabs">
            <button class="records-tab active">Transactions</button>
        </div>
        <?php if (empty($records)): ?>
            <div class="text-center text-muted mt-5" style="font-size:1.2rem;">No records yet</div>
        <?php else: ?>
            <?php foreach ($records as $record): ?>
                <div class="record-item">
                    <?= htmlspecialchars($record['title']) ?> <i class="bi bi-chevron-right record-arrow"></i>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php include_once __DIR__ . '/../SideBar_Right/SidebarRight.php'; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>  