<?php
session_start();
require '../../vendor/autoload.php';
require '../../Mailer.php';
include_once __DIR__ . '/../../database/conection_db.php';

$otpSent = isset($_SESSION['otp_sent']) && $_SESSION['otp_sent'];
$otpVerified = isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'];
$error = '';
$success = '';

// Persist form data
$formData = [
    'Username' => $_POST['Username'] ?? $_SESSION['signup_name'] ?? '',
    'Gender' => $_POST['Gender'] ?? '',
    'Email' => $_POST['Email'] ?? $_SESSION['signup_email'] ?? '',
    'Birthdate' => $_POST['Birthdate'] ?? ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle OTP sending
    if (isset($_POST['send_otp'])) {
        $email = $_POST['Email'];
        $name = $_POST['Username'];
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            $otp = rand(100000, 999999);
            $_SESSION['signup_otp'] = $otp;
            $_SESSION['signup_email'] = $email;
            $_SESSION['signup_name'] = $name;
            $_SESSION['otp_sent'] = true;

            $result = sendOtpMail($email, $name, $otp);
            if ($result === true) {
                $otpSent = true;
                $success = "OTP sent to your email!";
            } else {
                $error = "Message could not be sent. Mailer Error: $result";
            }
        }
    }

    // Handle OTP verification
    if (isset($_POST['verify_otp'])) {
        $userOtp = trim($_POST['otp']);
        if ($userOtp == $_SESSION['signup_otp']) {
            $_SESSION['otp_verified'] = true;
            $otpVerified = true;
            $success = "OTP verified successfully!";
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    }

    // Handle registration
    if (isset($_POST['register'])) {
        if (isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true) {
            $name = $_POST['Username'];
            $gender = $_POST['Gender'];
            $email = $_POST['Email'];
            $password = password_hash($_POST['Password'], PASSWORD_DEFAULT);
            $dob = $_POST['Birthdate'];

            $stmt = $conn->prepare("INSERT INTO patient (Username, Gender, Email, Password, Birthdate) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $gender, $email, $password, $dob);

            if ($stmt->execute()) {
                $success = "Registration successful!";
                unset($_SESSION['signup_otp'], $_SESSION['signup_email'], $_SESSION['signup_name'], 
                      $_SESSION['otp_verified'], $_SESSION['otp_sent']);
                header("Location: LogForm.php");
                exit;
            } else {
                $error = "Database error: " . $conn->error;
            }
            $stmt->close();
            $conn->close();
        } else {
            $error = "Please verify your OTP first.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - EMed Consultation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #e0ffe0 0%, #d0f5df 100%);
            min-height: 100vh;
        }
        .signup-card {
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .signup-card:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 16px 40px 0 rgba(31, 38, 135, 0.18);
        }
        .form-control:focus, .form-check-input:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.15rem rgba(40,167,69,.15);
        }
        .btn-primary, .btn-success {
            background-color: #43c97e !important;
            border-color: #43c97e !important;
            transition: background 0.2s, transform 0.2s;
            color: #fff !important;
        }
        .btn-primary:hover, .btn-success:hover {
            background: #2eb872 !important;
            border-color: #2eb872 !important;
            transform: scale(1.04);
            color: #fff !important;
        }
        .form-check-input:checked {
            background-color: #43c97e;
            border-color: #43c97e;
        }
        .fw-bold,
        .text-decoration-none {
            color: #14532d !important;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center align-items-center">
        <div class="col-lg-6">
            <div class="signup-card bg-white shadow-lg p-4">
                <a href="../../Homepage.php" class="btn-close position-absolute top-0 end-0 m-3" aria-label="Close" style="z-index:2;"></a>
                <div class="text-center mb-4">
                    <img src="../../Images/Signup.png" alt="Sign Up" class="mb-3" style="width:120px;">
                    <h2 class="mb-2" style="color: #2eb872; font-weight: bold">Patient Sign Up</h2>
                    <p class="text-muted mb-0">Create your EMed Consultation account</p>
                </div>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <form method="post" autocomplete="on" id="signupForm">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="Username" class="form-control" required 
                               value="<?= htmlspecialchars($formData['Username']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Gender" id="genderMale" 
                                       value="Male" <?= $formData['Gender'] == 'Male' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="genderMale">Male</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Gender" id="genderFemale" 
                                       value="Female" <?= $formData['Gender'] == 'Female' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="genderFemale">Female</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="Gender" id="genderOther" 
                                       value="Other" <?= $formData['Gender'] == 'Other' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="genderOther">Other</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <input type="email" name="Email" class="form-control" required 
                                   value="<?= htmlspecialchars($formData['Email']) ?>" 
                                   <?= $otpVerified ? 'readonly' : '' ?>>
                            <button type="submit" name="send_otp" class="btn btn-primary" 
                                    <?= $otpVerified ? 'disabled' : '' ?>>
                                <?= $otpSent ? 'Resend OTP' : 'Send OTP' ?>
                            </button>
                        </div>
                    </div>
                    <?php if ($otpSent): ?>
                        <div class="mb-3">
                            <label class="form-label">Enter OTP</label>
                            <div class="input-group">
                                <input type="text" name="otp" maxlength="6" class="form-control" 
                                       required pattern="\d{6}" 
                                       value="<?= isset($_POST['otp']) ? htmlspecialchars($_POST['otp']) : '' ?>">
                                <button type="submit" name="verify_otp" class="btn btn-success" 
                                        <?= $otpVerified ? 'disabled' : '' ?>>
                                    <?= $otpVerified ? 'Verified' : 'Verify OTP' ?>
                                </button>
                            </div>
                            <?php if ($otpVerified): ?>
                                <div class="form-text text-success mt-1">
                                    <i class="fas fa-check-circle"></i> OTP verified successfully!
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="Password" class="form-control" 
                               minlength="8" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="Birthdate" class="form-control" 
                               required value="<?= htmlspecialchars($formData['Birthdate']) ?>">
                    </div>
                    <button type="submit" name="register" class="btn btn-primary w-100 py-2" 
                            <?= !$otpVerified ? 'disabled' : '' ?>>Register Now</button>
                </form>
                <p class="text-center mt-3 mb-0">Already registered? 
                   <a href="LogForm.php" class="text-decoration-none" style="color:#43c97e;">Log in</a>
                </p>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('signupForm')?.addEventListener('submit', function(e) {
    const otpInput = document.querySelector('input[name="otp"]');
    if (otpInput) {
        const otpValue = otpInput.value;
        if (!/^\d{6}$/.test(otpValue)) {
            e.preventDefault();
            alert('Please enter a valid 6-digit OTP');
        }
    }
});
</script>
</body>
</html>