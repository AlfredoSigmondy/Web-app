<?php
require_once __DIR__ . '/../database/conection_db.php';

$patient_id = $_SESSION['Patient_id'] ?? null;
$appointments = [];
$doctors = [];
$notif = null;

// Fetch upcoming appointments for the patient
if ($patient_id) {
    $stmt = $conn->prepare("
        SELECT a.id, a.appointment_date, a.notes, a.status, d.Username as doctor_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.MedicID
        WHERE a.patient_id = ? AND a.appointment_date >= NOW()
        ORDER BY a.appointment_date ASC
        LIMIT 2
    ");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    $stmt->close();
}

// Fetch doctor names for messages
$stmt = $conn->prepare("SELECT MedicID, Username FROM doctors WHERE Status = 'Approved'");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $doctors[$row['MedicID']] = $row['Username'];
}
$stmt->close();

// Get patient username for the "Name" section
$patient_name ="";
if ($patient_id) {
    $stmt = $conn->prepare("SELECT Username FROM patient WHERE PatientID = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $stmt->bind_result($username);
    if ($stmt->fetch()) {
        $patient_name = htmlspecialchars($username);
    }
    $stmt->close();
}

// Notification for accepted or referred appointment
if ($patient_id) {
    $stmt = $conn->prepare("SELECT a.status, d.Username AS doctor_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.MedicID
        WHERE a.patient_id = ? AND (a.status = 'approved' OR a.status = 'referred')
        ORDER BY a.updated_at DESC LIMIT 1");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $stmt->bind_result($notif_status, $notif_doctor);
    if ($stmt->fetch()) {
        if ($notif_status === 'approved') {
            $notif = "Your appointment was <b>accepted</b> by Dr. $notif_doctor.";
        } elseif ($notif_status === 'referred') {
            $notif = "You were <b>referred</b> to another doctor by Dr. $notif_doctor.";
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Toggle Panel</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }

    .main-content {
      transition: margin-right 0.3s ease;
      padding: 20px;
    }

    .right-panel {
      position: fixed;
      top: 0;
      right: -320px;
      width: 300px;
      height: 100vh;
      background: #fff;
      border-radius: 2rem 0 0 2rem;
      box-shadow: 0 8px 32px 0 rgba(67, 201, 126, 0.1);
      padding: 24px 18px;
      transition: right 0.3s ease;
      z-index: 1050;
    }

    .right-panel.active {
      right: 0;
    }

    .main-content.pushed {
      margin-right: 300px;
    }

    .toggle-arrow {
      position: fixed;
      top: 50%;
      right: 0;
      transform: translateY(-50%);
      background-color: #43c97e;
      color: #fff;
      padding: 6px 10px;
      border-radius: 10px 0 0 10px;
      cursor: pointer;
      z-index: 1060;
    }

    .schedule-box, .appointment-box, .message-box {
      background: #e0ffe0;
      border-radius: 1rem;
      padding: 0.5rem 1rem;
    }

    .appointment-box.active {
      background: #43c97e;
      color: #fff;
    }

    .profile-icon {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: #e0ffe0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: #43c97e;
    }

    .ellipsis {
      float: right;
      color: #bbb;
      font-size: 1.2rem;
      margin-top: -2px;
    }
  </style>
</head>
<body>
  <?php if ($notif): ?>
    <div class="alert alert-success py-2 px-3 mb-3">
        <?= $notif ?>
    </div>
<?php endif; ?>


  <!-- Toggle Arrow -->
  <div class="toggle-arrow" id="toggleArrow">➤</div>

  <!-- Main Content -->
  <div class="main-content" id="mainContent">
  </div>

  <!-- Right Panel -->
  <div class="right-panel" id="rightPanel">
    <!-- Name -->
    <div class="fw-bold mb-2">
        Name <span class="profile-icon float-end"><i class="bi bi-person"></i></span>
    </div>
    <div class="mb-2">
        <span><?php echo $patient_name; ?></span>
    </div>

    <!-- Schedule -->
    <div class="mb-4">
        <div class="fw-bold">Schedule <span class="ellipsis bi bi-three-dots-vertical"></span></div>
        <div class="d-flex gap-2 mt-2">
            <?php if (empty($appointments)): ?>
                <div class="schedule-box text-center me-2">
                    <div class="schedule-type">No appointment yet</div>
                </div>
            <?php else: ?>
                <?php foreach ($appointments as $appointment): ?>
                    <?php
                        $date = new DateTime($appointment['appointment_date']);
                        $year = $date->format('Y');
                        $month = $date->format('F');
                        $day = $date->format('d');
                        $notes = htmlspecialchars($appointment['notes'] ?? 'Check-up');
                    ?>
                    <div class="schedule-box text-center me-2">
                        <div class="schedule-date"><?php echo $year; ?><br><span><?php echo $month; ?></span></div>
                        <div class="schedule-day fw-bold"><?php echo $day; ?></div>
                        <div class="schedule-type"><?php echo $notes; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Appointment -->
    <div class="mb-4">
        <div class="fw-bold">Appointment <span class="ellipsis bi bi-three-dots-vertical"></span></div>
        <?php if (empty($appointments)): ?>
            <div class="appointment-box mb-2">No appointments scheduled</div>
        <?php else: ?>
            <?php foreach ($appointments as $appointment): ?>
                <div class="appointment-box mb-2<?php echo ($appointment === reset($appointments)) ? ' active' : ''; ?>">
                    <?php echo htmlspecialchars($appointment['notes'] ?? 'Check-up'); ?><br>
                    <span class="text-muted small">Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?></span><br>
                    <span class="badge bg-info text-dark mt-1"><?php echo htmlspecialchars($appointment['status']); ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Message (keep your JS for dynamic messages, or show a placeholder) -->
    <div>
        <div class="fw-bold">Message <span class="ellipsis bi bi-three-dots-vertical"></span></div>
        <div class="message-list" id="messageList">
          <!-- Dynamic messages will be loaded here -->
        </div>
    </div>
  </div>

  <script>
    const toggleArrow = document.getElementById('toggleArrow');
    const rightPanel = document.getElementById('rightPanel');
    const mainContent = document.getElementById('mainContent');

    toggleArrow.addEventListener('click', () => {
      rightPanel.classList.toggle('active');
      mainContent.classList.toggle('pushed');
      toggleArrow.innerHTML = rightPanel.classList.contains('active') ? '◀' : '➤';
    });

    // Function to load messages via AJAX
    function loadMessages() {
      const xhr = new XMLHttpRequest();
      xhr.open('GET', '/path/to/your/api/for/messages', true);
      xhr.onload = function () {
        if (this.status === 200) {
          const messages = JSON.parse(this.responseText);
          const messageList = document.getElementById('messageList');
          messageList.innerHTML = ''; // Clear existing messages
          messages.forEach(msg => {
            const div = document.createElement('div');
            div.className = 'message-box mt-2';
            div.innerHTML = `
              <div class="small text-muted">${msg.doctor} • ${msg.timeAgo}</div>
              <div>${msg.content}</div>
            `;
            messageList.appendChild(div);
          });
        }
      };
      xhr.send();
    }

    // Load messages initially
    loadMessages();

    // Optionally, set an interval to refresh messages every minute
    setInterval(loadMessages, 60000);
  </script>

</body>
</html>
