<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - eMedConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; }
        .dashboard-card {
            border-radius: 16px;
            box-shadow: 0 2px 16px #0001;
            background: #fff;
            padding: 32px 24px;
            margin-bottom: 24px;
        }
        .dashboard-title {
            color: #14532d;
            font-weight: 700;
            margin-bottom: 24px;
        }
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2eb872;
        }
        .stat-label {
            color: #888;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include_once __DIR__ . '/../SideBar/DSidebar.php'; ?>
        <div class="flex-grow-1 p-4">
            <h2 class="dashboard-title mb-4">Welcome, Doctor!</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="dashboard-card text-center">
                        <div class="stat-value">
                            <?php
                            include_once __DIR__ . '/../database/conection_db.php';
                            $result = $conn->query("SELECT COUNT(*) as total FROM patient");
                            $row = $result ? $result->fetch_assoc() : ['total' => 0];
                            echo $row['total'];
                            ?>
                        </div>
                        <div class="stat-label">Total Patients</div>
                        <i class="bi bi-people" style="font-size:2rem;color:#43c97e;"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <div id="appointmentsCard" class="dashboard-card text-center" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#appointmentsModal">
                        <div class="stat-value">
                            <span id="myAppointmentsCount">
                            <?php
                                // Get doctor ID from session or fallback
                                $doctor_id = $_SESSION['doctor_id'] ?? 2005; // Replace 2005 with your test doctor ID if needed
                                $query = "SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ?";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("i", $doctor_id);
                                $stmt->execute();
                                $stmt->bind_result($total);
                                $stmt->fetch();
                                $stmt->close();
                                echo $total;
                            ?>
                            </span>
                        </div>
                        <div class="stat-label">My Appointments</div>
                        <i class="bi bi-calendar-event" style="font-size:2rem;color:#43c97e;"></i>
                    </div>
                </div>
                <!-- Updated card: Today's Appointments -->
                <div class="col-md-4">
                    <div class="dashboard-card text-center">
                        <div class="stat-value">
                            <?php
                                $today = date("Y-m-d");
                                $query = "SELECT COUNT(*) AS total FROM appointments 
                                          WHERE doctor_id = ? AND DATE(appointment_date) = ?";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("is", $doctor_id, $today);
                                $stmt->execute();
                                $stmt->bind_result($todayAppointments);
                                $stmt->fetch();
                                $stmt->close();
                                echo $todayAppointments;
                            ?>
                        </div>
                        <div class="stat-label">Today's Appointments</div>
                        <i class="bi bi-calendar-check" style="font-size:2rem;color:#43c97e;"></i>
                    </div>
                </div>
            </div>
            <div class="dashboard-card mt-4">
                <h5 class="mb-3">Quick Actions</h5>
                <a href="Message.php" class="btn btn-success me-2"><i class="bi bi-chat-dots"></i> Message Patients</a>
                <a href="Records.php" class="btn btn-info me-2"><i class="bi bi-file-earmark-medical"></i> View Records</a>
                <a href="fetch_appointments.php" class="btn btn-primary"><i class="bi bi-calendar-plus"></i> Manage Schedule</a>
            </div>
        </div>
         <?php include_once __DIR__ . '/../SideBar_Right/SidebarRight.php'; ?>
    </div>

    <!-- Appointments Modal -->
    <div class="modal fade" id="appointmentsModal" tabindex="-1" aria-labelledby="appointmentsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="appointmentsModalLabel">My Appointments</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="appointmentsModalBody"></div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function fetchAppointments() {
      fetch('../doctor/get_appointments.php?doctor_id=<?php echo $doctor_id; ?>')
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            renderAppointmentsWithActions(data.appointments);
          } else {
            document.getElementById('appointmentsModalBody').innerHTML = '<div class="alert alert-danger">Failed to load appointments.</div>';
          }
        });
    }

    document.getElementById('appointmentsCard').addEventListener('click', function() {
        fetch('../doctor/get_appointments.php?doctor_id=<?php echo $doctor_id; ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderAppointmentsWithActions(data.appointments);
                } else {
                    document.getElementById('appointmentsModalBody').innerHTML = 
                        '<div class="alert alert-danger">' + data.message + '</div>';
                }
                var modal = new bootstrap.Modal(document.getElementById('appointmentsModal'));
                modal.show();
            });
    });

    function renderAppointmentsWithActions(appointments) {
      if (!appointments || !appointments.length) {
        document.getElementById('appointmentsModalBody').innerHTML = '<div class="alert alert-info">No appointments found.</div>';
        return;
      }
      
      let html = `
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Patient</th>
                <th>Date & Time</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
      `;
      
      appointments.forEach(app => {
        html += `
          <tr>
            <td>${app.patient_name || 'N/A'}</td>
            <td>${new Date(app.appointment_date).toLocaleString()}</td>
            <td>${app.status || 'Pending'}</td>
            <td>${(app.notes || '').replace(/\n/g, '<br>')}</td>
            <td>
              <button class="btn btn-success btn-sm" onclick="handleAppointmentAction(${app.id}, 'accept')">Accept</button>
              <button class="btn btn-danger btn-sm" onclick="handleAppointmentAction(${app.id}, 'reject')">Reject</button>
            </td>
          </tr>
        `;
      });
      
      html += `
            </tbody>
          </table>
        </div>
      `;
      
      document.getElementById('appointmentsModalBody').innerHTML = html;
    }

    function handleAppointmentAction(appointmentId, action) {
      fetch('update_appointment_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
          appointment_id: appointmentId,
          action: action
        })
      })
      .then(res => res.json())
      .then(result => {
        if(result.success) {
          alert('Appointment ' + action + 'ed successfully!');
          document.getElementById('appointmentsCard').click();
          updateAppointmentsCount();
        } else {
          alert('Error: ' + (result.error || 'Operation failed'));
        }
      })
      .catch(() => alert('Network error'));
    }

    function updateAppointmentsCount() {
      fetch('../doctor/get_appointments.php?doctor_id=<?php echo $doctor_id; ?>')
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            document.getElementById('myAppointmentsCount').textContent = data.appointments.length;
          }
        });
    }
    </script>
</body>
</html>