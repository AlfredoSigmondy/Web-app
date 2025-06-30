<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
include_once __DIR__ . '/../../database/conection_db.php';
require_once __DIR__ . '/../../AuthMiddleware.php';

use Firebase\JWT\JWT;

$jwt_secret = '37gf2g928gf938gh9824fg9834fg9834fh348g7834gg4783gt34gf93g49gq3983589tg35809gt3ghelkgnbeioht';

$auth = new AuthMiddleware($jwt_secret);
$decodedToken = $auth->handle();
$error = '';

// Handle logout
if (isset($_GET['logout'])) {
    setcookie('jwt_token', '', time() - 3600, '/', '', true, true);
    session_destroy();
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// If no user and POST request, try login
if (!$decodedToken && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT MedicID, Password FROM doctors WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($medic_id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Create JWT token payload
            $payload = [
                'iss' => 'EMed',
                'iat' => time(),
                'exp' => time() + 3600,
                'sub' => $medic_id,
                'email' => $email,
                'role' => 'doctor'
            ];

            $jwt = JWT::encode($payload, $jwt_secret, 'HS256');

            // Set JWT as secure HttpOnly cookie
            setcookie('jwt_token', $jwt, [
                'expires' => time() + 3600,
                'path' => '/',
                'secure' => false, // Set to true if using HTTPS
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

            // Store ID in session
            $_SESSION['medic_id'] = $medic_id;

            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Invalid email or password.';
    }
}

// If user is logged in
if ($decodedToken) {
    $medic_id = $decodedToken->sub ?? null;
    $_SESSION['Medic_id'] = $medic_id;

    header('Location: /doctor/Dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Doctor Login - EMed Consultation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(120deg, #e0ffe0 0%, #d0f5df 100%);
            min-height: 100vh;
        }
        .login-card {
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px rgba(21, 196, 108, 0.15);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .login-card:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 16px 40px rgba(31, 38, 135, 0.18);
        }
        .btn-primary {
            background-color: #43c97e !important;
            border-color: #43c97e !important;
            color: #fff !important;
        }
        .btn-primary:hover {
            background: #2eb872 !important;
            border-color: #2eb872 !important;
            color: #fff !important;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center align-items-center">
        <div class="col-lg-5">
            <div class="login-card bg-white shadow-lg p-4 position-relative">
                <div class="text-center mb-4">
                    <img src="../../Images/Signup.png" alt="Login" class="mb-3" style="width:100px;" />
                    <h2 class="fw-bold mb-2" style="color: #2eb872;">Doctor Login</h2>
                </div>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="8" />
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
                </form>
                <p class="text-center mt-3 mb-0">Don't have an account? <a href="SignUp.php" class="text-decoration-none" style="color:#43c97e;">Sign up</a></p>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
