<?php
session_start();
include_once __DIR__ . '/../database/conection_db.php';

$doctor_id = $_SESSION['medic_id'] ?? null;
$doctor = null;
$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $username = $_POST['username'];
        $email = $_POST['email'];
        $birthdate = $_POST['birthdate'];
        $gender = $_POST['gender'];
        $license = $_POST['license'];
        $specialization = $_POST['specialization'];
        $contact = $_POST['contact'];

        $stmt = $conn->prepare("
            UPDATE doctors 
            SET Username = ?, Email = ?, Birthdate = ?, Gender = ?, 
                License = ?, Specialization = ?, ContactNumber = ?
            WHERE MedicID = ?
        ");
        $stmt->bind_param("sssssssi", $username, $email, $birthdate, $gender, $license, $specialization, $contact, $doctor_id);
        
        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating profile: " . $conn->error;
            $message_type = "danger";
        }
        $stmt->close();
    } elseif (isset($_POST['delete_account'])) {
        // Delete account (soft delete example)
        $stmt = $conn->prepare("UPDATE doctors SET Status = 'Inactive' WHERE MedicID = ?");
        $stmt->bind_param("i", $doctor_id);
        
        if ($stmt->execute()) {
            session_destroy();
            header("Location: login.php");
            exit();
        } else {
            $message = "Error deactivating account: " . $conn->error;
            $message_type = "danger";
        }
        $stmt->close();
    }
}

// Fetch doctor data
if ($doctor_id) {
    $stmt = $conn->prepare("
        SELECT MedicID, Username, Email, Birthdate, Gender, License, Specialization, ContactNumber, Status 
        FROM doctors 
        WHERE MedicID = ?
    ");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $stmt->bind_result($medic_id, $username, $email, $birthdate, $gender, $license, $specialization, $contact, $status);
    if ($stmt->fetch()) {
        $doctor = compact('medic_id', 'username', 'email', 'birthdate', 'gender', 'license', 'specialization', 'contact', 'status');
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - eMedConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="My_Profile.css">
</head>
<body>
<div class="d-flex">
    <?php include_once __DIR__ . '/../SideBar/DSidebar.php'; ?>

    <div class="flex-grow-1 main-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                            <?= $message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="profile-header mb-5">
                        <h3 class="fw-bold text-success mb-1">My Profile</h3>
                        <p class="text-muted">View and manage your profile information</p>
                    </div>

                    <div class="profile-card card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="text-center mb-5">
                                <div class="profile-avatar">
                                    <i class="bi bi-person"></i>
                                    <span class="profile-edit" title="Edit Profile Picture" data-bs-toggle="modal" data-bs-target="#avatarModal">
                                        <i class="bi bi-pencil"></i>
                                    </span>
                                </div>
                                <h4 class="mt-3 mb-0 fw-semibold"><?= htmlspecialchars($doctor['username'] ?? 'N/A') ?></h4>
                                <p class="text-muted mb-0"><?= htmlspecialchars($doctor['specialization'] ?? 'Specialization') ?></p>
                            </div>

                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="profile-section mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="profile-section-title mb-0">Personal Information</h5>
                                            </div>
                                            
                                            <div class="profile-info-item">
                                                <label class="profile-label">Full Name</label>
                                                <input type="text" class="form-control profile-input" name="username" 
                                                    value="<?= htmlspecialchars($doctor['username'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="profile-info-item">
                                                <label class="profile-label">Gender</label>
                                                <select class="form-select profile-input" name="gender">
                                                    <option value="Male" <?= ($doctor['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                                    <option value="Female" <?= ($doctor['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                                    <option value="Other" <?= ($doctor['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                                </select>
                                            </div>
                                            
                                            <div class="profile-info-item">
                                                <label class="profile-label">Date of Birth</label>
                                                <input type="date" class="form-control profile-input" name="birthdate" 
                                                    value="<?= htmlspecialchars($doctor['birthdate'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="profile-section mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="profile-section-title mb-0">Professional Information</h5>
                                            </div>
                                            
                                            <div class="profile-info-item">
                                                <label class="profile-label">Specialization</label>
                                                <input type="text" class="form-control profile-input" name="specialization" 
                                                    value="<?= htmlspecialchars($doctor['specialization'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="profile-info-item">
                                                <label class="profile-label">License Number</label>
                                                <input type="text" class="form-control profile-input" name="license" 
                                                    value="<?= htmlspecialchars($doctor['license'] ?? '') ?>">
                                            </div>
                                            
                                            <div class="profile-info-item">
                                                <label class="profile-label">Status</label>
                                                <input type="text" class="form-control profile-input" value="<?= htmlspecialchars($doctor['status'] ?? 'N/A') ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="profile-section">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="profile-section-title mb-0">Contact Information</h5>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="profile-info-item">
                                                <label class="profile-label">Email Address</label>
                                                <input type="email" class="form-control profile-input" name="email" 
                                                    value="<?= htmlspecialchars($doctor['email'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="profile-info-item">
                                                <label class="profile-label">Phone Number</label>
                                                <input type="tel" class="form-control profile-input" name="contact" 
                                                    value="<?= htmlspecialchars($doctor['contact'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="bi bi-trash me-1"></i> Delete Account
                                    </button>
                                    <button type="submit" name="update_profile" class="btn btn-success">
                                        <i class="bi bi-save me-1"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/../Sidebar_Right/SidebarRight.php'; ?>
</div>

<!-- Avatar Update Modal -->
<div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="avatarModalLabel">Update Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Select new image</label>
                        <input class="form-control" type="file" id="avatar" name="avatar" accept="image/*">
                    </div>
                    <div class="text-center">
                        <img src="#" id="avatarPreview" class="img-thumbnail d-none" style="max-width: 200px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_avatar" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="deleteModalLabel">Confirm Account Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete your account? This action cannot be undone.</p>
                <p class="text-danger"><strong>Warning:</strong> All your data will be permanently removed.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="">
                    <button type="submit" name="delete_account" class="btn btn-danger">Delete My Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Preview avatar before upload
    document.getElementById('avatar').addEventListener('change', function(e) {
        const preview = document.getElementById('avatarPreview');
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            }
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>