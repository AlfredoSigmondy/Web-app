<?php
include_once __DIR__ . '/../database/conection_db.php'; // Corrected typo in "connection_db.php"
$patient_id = $_SESSION['patient_id'] ?? null;
$already_filled = false;
$error = "";

// Validate patient_id
if (!$patient_id) {
    $error = "No patient ID found. Please log in.";
}

// Check if form was just submitted
if (isset($_SESSION['medical_info_submitted'])) {
    $already_filled = true;
    unset($_SESSION['medical_info_submitted']);
} elseif ($patient_id) {
    $query = "SELECT 1 FROM patient_medical_info WHERE patient_id = ? LIMIT 1";
    if (!$conn) {
        $error = "Database connection failed.";
    } else {
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $patient_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $already_filled = true;
            }
            $stmt->close();
        } else {
            $error = "Failed to prepare query.";
        }
    }
}

// Handle form submission
if (isset($_POST['submit_medical_info']) && $patient_id && !$already_filled) {
    $medicalFilePath = null;

    // Handle file upload if exists
    if (isset($_FILES['medical_file']) && $_FILES['medical_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['medical_file']['tmp_name'];
        $fileName = basename($_FILES['medical_file']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];

        if (in_array($fileExt, $allowed)) {
            $uploadDir = __DIR__ . '/../uploads/medical_reports/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newFileName = uniqid("med_", true) . "." . $fileExt;
            $filePath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmp, $filePath)) {
                $medicalFilePath = 'uploads/medical_reports/' . $newFileName;
            } else {
                $error = "Failed to upload file.";
            }
        } else {
            $error = "Invalid file type. Only PDF, JPG, JPEG, and PNG are allowed.";
        }
    } elseif (isset($_FILES['medical_file']) && $_FILES['medical_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $error = "File upload error: " . $_FILES['medical_file']['error'];
    }

    // If no errors so far, proceed with database insertion
    if (empty($error)) {
        $stmt = $conn->prepare("
            INSERT INTO patient_medical_info (
                patient_id, height_cm, weight_kg, blood_pressure, heart_rate,
                blood_type, rh_factor, allergy_medications, allergy_foods, allergy_environmental,
                smoking_status, alcohol_use, exercise_habits, diet, medical_file
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if ($stmt) {
            $stmt->bind_param(
                "iddsissssssssss",
                $patient_id,
                $_POST['height_cm'],
                $_POST['weight_kg'],
                $_POST['blood_pressure'],
                $_POST['heart_rate'],
                $_POST['blood_type'],
                $_POST['rh_factor'],
                $_POST['allergy_medications'],
                $_POST['allergy_foods'],
                $_POST['allergy_environmental'],
                $_POST['smoking_status'],
                $_POST['alcohol_use'],
                $_POST['exercise_habits'],
                $_POST['diet'],
                $medicalFilePath
            );

            if ($stmt->execute()) {
                $_SESSION['medical_info_submitted'] = true;
                header("Location: /Dashboard.php");
                exit;
            } else {
                $error = "Failed to save medical information: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Failed to prepare database statement.";
        }
    }
}
?>

<?php if (!$already_filled && $patient_id): ?>
<!-- External CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

<style>
.modal-content {
    border-radius: 20px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}
.modal-header {
    background-color: #43c97e;
    color: white;
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
}
.modal-footer button {
    background-color: #43c97e;
    color: white;
    border-radius: 24px;
}
.modal-footer button:hover {
    background-color: #38b66c;
}
.form-control, .form-select {
    border-radius: 12px;
    border: 1px solid #ced4da;
}
.form-control:focus, .form-select:focus {
    border-color: #43c97e;
    box-shadow: 0 0 0 0.2rem rgba(67, 201, 126, 0.25);
}
label {
    font-weight: 500;
    color: #495057;
}
</style>

<!-- Animated Modal -->
<div class="modal show fade animate__animated animate__fadeInDown" tabindex="-1" style="display:block; background-color:rgba(0,0,0,0.5);" data-bs-backdrop="static">
  <div class="modal-dialog" style="max-width: 800px;">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Medical Information</h5>
        </div>
        <div class="modal-body">
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <div class="row g-3">
            <div class="col-md-4">
              <label>Height (cm)</label>
              <input type="number" step="0.1" name="height_cm" class="form-control" value="<?= htmlspecialchars($_POST['height_cm'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label>Weight (kg)</label>
              <input type="number" step="0.1" name="weight_kg" class="form-control" value="<?= htmlspecialchars($_POST['weight_kg'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label>Blood Pressure</label>
              <input type="text" name="blood_pressure" class="form-control" value="<?= htmlspecialchars($_POST['blood_pressure'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label>Heart Rate (bpm)</label>
              <input type="number" name="heart_rate" class="form-control" value="<?= htmlspecialchars($_POST['heart_rate'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label>Blood Type</label>
              <select name="blood_type" class="form-select">
                <option value="">Select</option>
                <option value="A+" <?= ($_POST['blood_type'] ?? '') === 'A+' ? 'selected' : '' ?>>A+</option>
                <option value="A−" <?= ($_POST['blood_type'] ?? '') === 'A−' ? 'selected' : '' ?>>A−</option>
                <option value="B+" <?= ($_POST['blood_type'] ?? '') === 'B+' ? 'selected' : '' ?>>B+</option>
                <option value="B−" <?= ($_POST['blood_type'] ?? '') === 'B−' ? 'selected' : '' ?>>B−</option>
                <option value="AB+" <?= ($_POST['blood_type'] ?? '') === 'AB+' ? 'selected' : '' ?>>AB+</option>
                <option value="AB−" <?= ($_POST['blood_type'] ?? '') === 'AB−' ? 'selected' : '' ?>>AB−</option>
                <option value="O+" <?= ($_POST['blood_type'] ?? '') === 'O+' ? 'selected' : '' ?>>O+</option>
                <option value="O−" <?= ($_POST['blood_type'] ?? '') === 'O−' ? 'selected' : '' ?>>O−</option>
              </select>
            </div>
            <div class="col-md-4">
              <label>Rh Factor</label>
              <select name="rh_factor" class="form-select">
                <option value="positive" <?= ($_POST['rh_factor'] ?? '') === 'positive' ? 'selected' : '' ?>>Positive</option>
                <option value="negative" <?= ($_POST['rh_factor'] ?? '') === 'negative' ? 'selected' : '' ?>>Negative</option>
              </select>
            </div>
            <div class="col-md-6">
              <label>Allergies (Medications)</label>
              <input type="text" name="allergy_medications" class="form-control" value="<?= htmlspecialchars($_POST['allergy_medications'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label>Allergies (Foods)</label>
              <input type="text" name="allergy_foods" class="form-control" value="<?= htmlspecialchars($_POST['allergy_foods'] ?? '') ?>">
            </div>
            <div class="col-md-12">
              <label>Allergies (Environmental)</label>
              <input type="text" name="allergy_environmental" class="form-control" value="<?= htmlspecialchars($_POST['allergy_environmental'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label>Smoking Status</label>
              <select name="smoking_status" class="form-select">
                <option value="no" <?= ($_POST['smoking_status'] ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                <option value="yes" <?= ($_POST['smoking_status'] ?? '') === 'yes' ? 'selected' : '' ?>>Yes</option>
                <option value="past" <?= ($_POST['smoking_status'] ?? '') === 'past' ? 'selected' : '' ?>>Past</option>
              </select>
            </div>
            <div class="col-md-4">
              <label>Alcohol Use</label>
              <input type="text" name="alcohol_use" class="form-control" value="<?= htmlspecialchars($_POST['alcohol_use'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label>Exercise Habits</label>
              <input type="text" name="exercise_habits" class="form-control" value="<?= htmlspecialchars($_POST['exercise_habits'] ?? '') ?>">
            </div>
            <div class="col-md-12">
              <label>Diet</label>
              <input type="text" name="diet" class="form-control" value="<?= htmlspecialchars($_POST['diet'] ?? '') ?>">
            </div>
            <div class="col-md-12">
              <label for="medical_file">Upload Medical Report (optional)</label>
              <input type="file" name="medical_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
              <small class="form-text text-muted">If you are unsure about your medical details, you can upload your previous medical report instead.</small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="submit_medical_info" class="btn btn-success">Save & Continue</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Include Bootstrap JS for modal functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php else: ?>
    <?php if (!$patient_id): ?>
        <div class="alert alert-danger">Please log in to access this form.</div>
    <?php elseif ($already_filled): ?>
        <div class="alert alert-info">Medical information already submitted.</div>
        <script>
            window.location.href = '/Dashboard.php';
        </script>
    <?php endif; ?>
<?php endif; ?>