<?php
session_start();
$showWelcome = false;
if (isset($_SESSION['welcome_shown']) && $_SESSION['welcome_shown'] === false) {
    $showWelcome = true;
    $_SESSION['welcome_shown'] = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - eMedConnect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="Dashboard.css" rel="stylesheet">
</head>
<body>
    <style>
        .butttn{
            background-color: #2eb872;
            color: white;
            font-weight: bold;
            border-radius: 1.5rem;
            padding: 10px 20px;
            border: none;
        }
    </style>
    
<?php if ($showWelcome): ?>
<div id="welcomeOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-50" style="z-index: 1050;">
    <div class="bg-white text-dark p-5 rounded-4 shadow-lg text-center animate__animated animate__fadeIn" style="max-width: 400px; width: 90%;">
    
    <h2 class="mb-3 animate__animated animate__fadeInDown">Welcome to eMedConnect</h2>
        <p class="mb-4 animate__animated animate__fadeInUp">Your one-stop platform for health consultations and medicines</p>
        <button class="butttn" onclick="continueToDashboard()">Continue</button>
    </div>
</div>
<?php endif; ?>
<div id="medicalFormContainer" style="display: none;">
    <?php include 'medical_form.php'; ?>
</div>
<div class="d-flex" style="min-height: 100vh;">
    <?php include_once __DIR__ . '/../SideBar/Sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
        <div class="dashboard-banner mb-4">
            <div>
                <h3 class="fw-bold mb-2">Talk to a doctor now!</h3>
                <div class="mb-3">Consult with a general physician via chat or video instantly.</div>
                <button class="btn btn-light fw-semibold me-2" onclick="window.location.href = 'Message.php';">Get Started</button>
                <button class="btn btn-outline-light fw-semibold">Learn More</button>
            </div>
            <img src="../Images/doctor_colored.png" alt="Doctors">
        </div>
        <div class="d-flex align-items-center mb-2">
            <span class="feature-title">Feature</span>  
        </div>
        <div class="row g-4 mb-4">
            <div class="col-md-6" onclick="window.location.href = 'Consultation.php';">
                <div class="feature-card position-relative">
                    <span class="feature-badge">!</span>
                    <img src="../Images/consultation.png" alt="Consultation">
                    <div>Consultation</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-card" onclick="window.location.href = 'Doctor.php';">
                    <img src="../Images/doctor.png" alt="Doctor">
                    <div>Doctor</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-card" onclick="window.location.href = 'MedMarket.php';">
                    <img src="../Images/medicine.png" alt="Medicine">
                    <div>Medicine Market</div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once __DIR__ . '/../SideBar_Right/SidebarRight.php'; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function continueToDashboard() {
    const overlay = document.getElementById('welcomeOverlay');
    const formContainer = document.getElementById('medicalFormContainer');

    overlay.classList.add('animate__animated', 'animate__fadeOut');

    overlay.addEventListener('animationend', () => {
        overlay.style.display = 'none';
        formContainer.style.display = 'block';
    });

    // Set the flag to prevent showing again
    localStorage.setItem('emedconnect_welcome_shown', 'true');
}
</script>
</body>
</html>