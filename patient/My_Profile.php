<?php
session_start();
include_once __DIR__ . '/../database/conection_db.php';

$user_id = $_SESSION['Patient_id'] ?? null;
$user = null;
$medical_info = null;

if ($user_id) {
    // Get user info
    $stmt = $conn->prepare("SELECT PatientID, Username, Email, Birthdate, Gender FROM patient WHERE PatientID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($patient_id, $username, $email, $birthdate, $gender);
    if ($stmt->fetch()) {
        $user = [
            'patient_id' => $patient_id,
            'username' => $username,
            'email' => $email,
            'birthdate' => $birthdate,
            'gender' => $gender
        ];
    }
    $stmt->close();

    // Get medical info
    $stmt2 = $conn->prepare("
        SELECT height_cm, weight_kg, blood_pressure, heart_rate,
               blood_type, rh_factor, allergy_medications, allergy_foods, allergy_environmental,
               smoking_status, alcohol_use, exercise_habits, diet
        FROM patient_medical_info
        WHERE patient_id = ?
    ");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $result = $stmt2->get_result();
    if ($result && $result->num_rows > 0) {
        $medical_info = $result->fetch_assoc();
    }
    $stmt2->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="My_Profile.css" rel="stylesheet" />
</head>
<body>
<div class="d-flex" style="min-height: 100vh;">
    <?php include_once __DIR__ . '/../SideBar/Sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
        <div class="container-fluid py-4">
            <div class="profile-header">Profile details</div>
            <div class="row justify-content-center">
                <!-- Personal Info + Avatar -->
                <div class="col-lg-6 col-md-12">
                    <div class="text-center mb-4">
                        <div class="profile-avatar d-inline-block position-relative">
                            <img src="../Images/Denisoon.jpg" alt="Profile Avatar" class="img-fluid rounded-circle" style="width: 120px; height: 120px; object-fit: cover;" />
                        </div>
                    </div>
                    <div class="profile-section-title mb-2">Personal Information</div>
                    <a href="#" class="profile-edit-link mb-3 d-block">Edit</a>
                    <div class="info-row">
                        <div class="profile-info-label"><i class="bi bi-card-list"></i> Patient ID</div>
                        <div class="profile-info-value"><?= htmlspecialchars($user['patient_id'] ?? '--------------------') ?></div>
                    </div>
                    <div class="info-row">
                        <div class="profile-info-label"><i class="bi bi-person"></i> Username</div>
                        <div class="profile-info-value"><?= htmlspecialchars($user['username'] ?? '') ?></div>
                    </div>
                    <div class="info-row">
                        <div class="profile-info-label"><i class="bi bi-envelope"></i> Email</div>
                        <div class="profile-info-value"><?= htmlspecialchars($user['email'] ?? '--------------------') ?></div>
                    </div>
                    <div class="info-row">
                        <div class="profile-info-label"><i class="bi bi-calendar"></i> Birthdate</div>
                        <div class="profile-info-value"><?= htmlspecialchars($user['birthdate'] ?? '--------------------') ?></div>
                    </div>
                    <div class="info-row">
                        <div class="profile-info-label"><i class="bi bi-gender-ambiguous"></i> Gender</div>
                        <div class="profile-info-value"><?= htmlspecialchars($user['gender'] ?? '--------------------') ?></div>
                    </div>
                </div>

                <!-- Medical Info -->
                <?php if ($medical_info): ?>
                <div class="col-lg-6 col-md-12">
                    <div class="medical-info-title d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-heart-pulse"></i> Medical Information</div>
                        <button type="button" data-bs-toggle="collapse" data-bs-target="#medicalInfoCard" aria-expanded="true" aria-controls="medicalInfoCard" class="btn btn-link p-0">
                            <i class="bi bi-chevron-up" id="collapseIcon"></i>
                        </button>
                    </div>
                    <div class="medical-info-card collapse show" id="medicalInfoCard">
                        <div class="info-row">
                            <div class="profile-info-label">Height (cm)</div>
                            <div class="profile-info-value"><?= htmlspecialchars($medical_info['height_cm']) ?></div>
                        </div>
                        <div class="info-row">
                            <div class="profile-info-label">Weight (kg)</div>
                            <div class="profile-info-value"><?= htmlspecialchars($medical_info['weight_kg']) ?></div>
                        </div>
                        <div class="info-row">
                            <div class="profile-info-label">Blood Pressure</div>
                            <div class="profile-info-value"><?= htmlspecialchars($medical_info['blood_pressure']) ?></div>
                        </div>
                        <div class="info-row">
                            <div class="profile-info-label">Heart Rate (bpm)</div>
                            <div class="profile-info-value"><?= htmlspecialchars($medical_info['heart_rate']) ?></div>
                        </div>
                        <div class="info-row">
                            <div class="profile-info-label">Blood Type</div>
                            <div class="profile-info-value"><?= htmlspecialchars($medical_info['blood_type']) . ' (' . ucfirst($medical_info['rh_factor']) . ')' ?></div>
                        </div>
                        <div class="info-row">
                            <div class="profile-info-label">Allergies (Medications)</div>
                            <div class="profile-info-value"><?= htmlspecialchars($medical_info['allergy_medications'] ?: 'None') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="profile-info-label">Allergies (Foods)</div>
                            <div class="profile-info-value"><?= htmlspecialchars($medical_info['allergy_foods'] ?: 'None') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="profile-info-label">Allergies (Environmental)</div>
                            <div class="profile-info-value"><?= htmlspecialchars($medical_info['allergy_environmental'] ?: 'None') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="profile-info-label">Smoking Status</div>
                            <div class="profile-info-value"><?= ucfirst(htmlspecialchars($medical_info['smoking_status'])) ?></div>
                        </div>
                        <div class="info-row">
                            <div class="profile-info-label">Alcohol Use</div>
                            <div class="profile-info-value"><?= htmlspecialchars($medical_info['alcohol_use'] ?: 'Not specified') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="profile-info-label">Exercise Habits</div>
                            <div class="profile-info-value"><?= htmlspecialchars($medical_info['exercise_habits'] ?: 'Not specified') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="profile-info-label">Diet</div>
                            <div class="profile-info-value"><?= htmlspecialchars($medical_info['diet'] ?: 'Not specified') ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include_once __DIR__ . '/../SideBar_Right/SidebarRight.php'; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const collapseIcon = document.getElementById('collapseIcon');
    const collapseElement = document.getElementById('medicalInfoCard');

    collapseElement.addEventListener('hide.bs.collapse', () => {
        collapseIcon.classList.remove('bi-chevron-up');
        collapseIcon.classList.add('bi-chevron-down');
    });

    collapseElement.addEventListener('show.bs.collapse', () => {
        collapseIcon.classList.remove('bi-chevron-down');
        collapseIcon.classList.add('bi-chevron-up');
    });
</script>
</body>
</html>
