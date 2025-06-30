<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doctors - eMedConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Doctor.css">
    <style>
        .doctor-card {
            background-color: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .doctor-avatar i {
            font-size: 50px;
            color: #198754;
        }
        .doctor-info {
            flex-grow: 1;
        }
        .btn-green {
            background-color: #198754;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
        }
        .search-bar {
            flex-grow: 1;
        }
        .main-content {
            overflow-y: auto;
            max-height: 100vh;
            padding: 1rem;
        }
        .time-slot-btn {
            min-width: 80px;
            margin: 5px;
        }
        .time-slot-btn.selected {
            background-color: #198754;
            color: white;
        }
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }
        /* Modal improvements */
        .modal-content {
          border-radius: 18px;
          box-shadow: 0 8px 32px rgba(0,0,0,0.18);
          background: #fff;
          padding-bottom: 0.5rem;
        }
        .modal-header {
          border-bottom: none;
          padding-bottom: 0;
        }
        .modal-title {
          font-weight: 700;
          font-size: 1.35rem;
          color: #222;
        }
        #appointmentForm label {
          font-weight: 500;
          margin-bottom: 4px;
        }
        #calendarContainer {
          background: #f6f8fa;
          border-radius: 14px;
          padding: 1rem 0.5rem 0.5rem 0.5rem;
          margin-bottom: 1rem;
          box-shadow: 0 2px 8px rgba(25,135,84,0.05);
        }
        #calendarMonthYear {
          font-weight: 600;
          font-size: 1.1rem;
          color: #198754;
        }
        #calendarGrid {
          display: grid;
          grid-template-columns: repeat(7, 1fr);
          gap: 6px;
          margin-bottom: 8px;
          margin-top: 6px;
        }
        .calendar-day {
          padding: 10px 0;
          border-radius: 50%;
          text-align: center;
          cursor: pointer;
          background: #e9ecef;
          min-width: 36px;
          min-height: 36px;
          font-size: 1rem;
          color: #222;
          transition: background 0.18s, color 0.18s, box-shadow 0.18s;
          border: 2px solid transparent;
        }
        .calendar-day.available {
          background: #e6f7ee;
          color: #198754;
          font-weight: 600;
          border: 2px solid #19875433;
        }
        .calendar-day.available:hover {
          background: #198754;
          color: #fff;
          box-shadow: 0 2px 8px #19875433;
        }
        .calendar-day.selected {
          background: #198754;
          color: #fff;
          border: 2px solid #198754;
          box-shadow: 0 2px 8px #19875433;
        }
        .calendar-day:not(.available) {
          opacity: 0.4;
          cursor: default;
        }
        #timeSlots {
          margin-top: 18px;
          display: flex;
          flex-wrap: wrap;
          gap: 10px;
          justify-content: flex-start;
        }
        .time-slot {
          padding: 8px 22px;
          border-radius: 22px;
          background: #e9f7ef;
          cursor: pointer;
          border: 1.5px solid #198754;
          color: #198754;
          font-weight: 500;
          font-size: 1rem;
          margin-bottom: 4px;
          transition: background 0.18s, color 0.18s, border 0.18s;
        }
        .time-slot.selected, .time-slot:hover {
          background: #198754;
          color: #fff;
          border: 1.5px solid #198754;
        }
        #formError {
          font-size: 0.98rem;
        }
        .btn-primary {
          background: #14532d;
          border: none;
          border-radius: 10px;
          font-weight: 600;
          font-size: 1.1rem;
          padding: 12px 0;
          margin-top: 10px;
        }
        .btn-primary:active, .btn-primary:focus {
          background: #198754;
        }
        @media (max-width: 600px) {
          .modal-content { padding: 0.5rem; }
          #calendarContainer { padding: 0.5rem 0.2rem; }
          #calendarGrid { font-size: 0.95rem; }
          .time-slot { font-size: 0.95rem; padding: 7px 12px; }
        }
    </style>
</head>
<body>
<div class="d-flex" style="min-height: 100vh;">
    <?php include_once __DIR__ . '/../SideBar/Sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
        <?php
        include_once __DIR__ . '/../database/conection_db.php';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $limit = 5;
        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $offset = ($page - 1) * $limit;

        $countSql = "SELECT COUNT(*) as total FROM doctors" . (!empty($search) ? " WHERE Specialization LIKE '%$search%' OR Username LIKE '%$search%'" : "");
        $countResult = $conn->query($countSql);
        $totalDoctors = $countResult->fetch_assoc()['total'];
        $totalPages = ceil($totalDoctors / $limit);

        $sql = "SELECT MedicID, Username, Email, Specialization, Gender, Birthdate, ContactNumber FROM doctors WHERE Status = 'Approved'";
        if (!empty($search)) {
            $sql .= " WHERE Specialization LIKE '%$search%' OR Username LIKE '%$search%'";
        }
        $sql .= " LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);
        ?>

        <form method="GET" class="d-flex align-items-center mb-4" style="gap: 0.5rem;">
            <input type="text" class="form-control search-bar" name="search" placeholder="Search specialization or doctor name..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-light" type="submit"><i class="bi bi-search"></i></button>
        </form>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="doctor-card">
                    <div class="doctor-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="doctor-info">
                        <div class="doctor-name fw-bold">Dr. <?= htmlspecialchars($row['Username']) ?></div>
                        <div class="doctor-title text-muted"><?= htmlspecialchars($row['Specialization']) ?></div>
                        <div class="doctor-exp"><?= ($row['Birthdate']) ? (date('Y') - date('Y', strtotime($row['Birthdate']))) . " Years of Experience" : "Experience Unknown" ?></div>
                        <div class="doctor-email text-secondary"><?= htmlspecialchars($row['Email']) ?></div>
                        <div class="doctor-contact">Contact: <?= htmlspecialchars($row['ContactNumber']) ?></div>
                    </div>
                    <button class="btn btn-green book-btn" data-doctor-id="<?= htmlspecialchars($row['MedicID']) ?>" data-bs-toggle="modal" data-bs-target="#bookAppointmentModal">Book Appointment</button>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No doctors found.</p>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center mt-4">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
    <?php include_once __DIR__ . '/../SideBar_Right/SidebarRight.php'; ?>
</div>

<!-- Appointment Modal -->
<div class="modal fade" id="bookAppointmentModal" tabindex="-1" aria-labelledby="bookAppointmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bookAppointmentModalLabel">Book Appointment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="appointmentForm">
          <input type="hidden" id="doctorId" name="doctor_id">
          <div class="mb-3">
            <label for="patientName" class="form-label">Your Name</label>
            <input type="text" class="form-control" id="patientName" name="patient_name" required>
          </div>
          <div class="mb-3">
            <label for="notes" class="form-label">Reason for Appointment</label>
            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Available Dates & Times</label>
            <div id="calendarContainer">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 id="calendarMonthYear"></h6>
                <div>
                  <button type="button" class="btn btn-sm btn-light" id="prevMonthBtn"><i class="bi bi-chevron-left"></i></button>
                  <button type="button" class="btn btn-sm btn-light" id="nextMonthBtn"><i class="bi bi-chevron-right"></i></button>
                </div>
              </div>
              <div id="calendarGrid"></div>
              <div id="timeSlots"></div>
            </div>
            <input type="hidden" id="selectedDateInput" name="selected_date">
          </div>
          <div id="formError" class="text-danger mb-2" style="display:none;"></div>
          <button type="submit" class="btn btn-primary w-100" id="submitBtn">Confirm Appointment</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
// Toast notification
function showToast(message, isSuccess = true) {
  const toast = document.createElement('div');
  toast.className = `toast align-items-center text-white ${isSuccess ? 'bg-success' : 'bg-danger'} border-0`;
  toast.style.position = 'fixed';
  toast.style.bottom = '20px';
  toast.style.right = '20px';
  toast.style.zIndex = '9999';
  toast.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  `;
  document.body.appendChild(toast);
  const bsToast = new bootstrap.Toast(toast);
  bsToast.show();
  setTimeout(() => toast.remove(), 4000);
}

let availableDates = {};
let selectedDate = null;
let selectedTime = null;
let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth() + 1;

// Open modal and fetch availability
document.querySelectorAll('.book-btn').forEach(button => {
  button.addEventListener('click', function() {
    const doctorId = this.getAttribute('data-doctor-id');
    document.getElementById('doctorId').value = doctorId;
    document.getElementById('appointmentForm').reset();
    document.getElementById('formError').style.display = 'none';
    selectedDate = null;
    selectedTime = null;
    fetchAvailability(doctorId, currentYear, currentMonth);
    new bootstrap.Modal(document.getElementById('bookAppointmentModal')).show();
  });
});

document.getElementById('prevMonthBtn').addEventListener('click', function() {
  currentMonth--;
  if (currentMonth < 1) {
    currentMonth = 12;
    currentYear--;
  }
  fetchAvailability(document.getElementById('doctorId').value, currentYear, currentMonth);
});
document.getElementById('nextMonthBtn').addEventListener('click', function() {
  currentMonth++;
  if (currentMonth > 12) {
    currentMonth = 1;
    currentYear++;
  }
  fetchAvailability(document.getElementById('doctorId').value, currentYear, currentMonth);
});

function fetchAvailability(doctorId, year, month) {
  document.getElementById('calendarGrid').innerHTML = '<span class="text-muted">Loading...</span>';
  fetch(`get_available_dates.php?MedicId=${doctorId}&year=${year}&month=${month}`)
    .then(response => response.json())
    .then(data => {
      availableDates = data.slots || {};
      renderCalendar();
    })
    .catch(() => {
      document.getElementById('calendarGrid').innerHTML = '<span class="text-danger">Failed to load dates</span>';
    });
}

function renderCalendar() {
  const calendarGrid = document.getElementById('calendarGrid');
  const monthYear = document.getElementById('calendarMonthYear');
  calendarGrid.innerHTML = '';
  monthYear.textContent = '';

  const dateObj = new Date(currentYear, currentMonth - 1, 1);
  monthYear.textContent = dateObj.toLocaleString('default', { month: 'long', year: 'numeric' });

  // Add day headers
  const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  daysOfWeek.forEach(day => {
    const header = document.createElement('div');
    header.textContent = day;
    header.style.fontWeight = 'bold';
    header.style.textAlign = 'center';
    calendarGrid.appendChild(header);
  });

  const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
  const firstDayOfWeek = dateObj.getDay();

  // Empty cells for days before the 1st
  for (let i = 0; i < firstDayOfWeek; i++) {
    calendarGrid.appendChild(document.createElement('div'));
  }
  for (let d = 1; d <= daysInMonth; d++) {
    const dayStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    const dayEl = document.createElement('div');
    dayEl.className = 'calendar-day';
    dayEl.textContent = d;
    if (availableDates[dayStr] && availableDates[dayStr].length > 0) {
      dayEl.classList.add('available');
      dayEl.onclick = () => selectDate(dayStr, dayEl);
    }
    if (selectedDate === dayStr) dayEl.classList.add('selected');
    calendarGrid.appendChild(dayEl);
  }
  if (Object.keys(availableDates).length === 0) {
    calendarGrid.innerHTML = '<div class="text-muted">No available dates for this month</div>';
    document.getElementById('timeSlots').innerHTML = '';
  }
}

function selectDate(dateStr, dayEl) {
  selectedDate = dateStr;
  document.querySelectorAll('.calendar-day').forEach(el => el.classList.remove('selected'));
  dayEl.classList.add('selected');
  renderTimeSlots(dateStr);
}

function renderTimeSlots(dateStr) {
  const timeSlotsDiv = document.getElementById('timeSlots');
  timeSlotsDiv.innerHTML = '';
  selectedTime = null;
  if (!availableDates[dateStr] || availableDates[dateStr].length === 0) {
    timeSlotsDiv.innerHTML = '<span class="text-muted">No time slots</span>';
    return;
  }
  availableDates[dateStr].forEach(time => {
    const slot = document.createElement('span');
    slot.className = 'time-slot btn btn-outline-primary m-1';
    slot.textContent = time;
    slot.onclick = () => selectTimeSlot(dateStr, time, slot);
    timeSlotsDiv.appendChild(slot);
  });
}

function selectTimeSlot(dateStr, time, slotEl) {
  selectedTime = time;
  document.querySelectorAll('.time-slot').forEach(el => el.classList.remove('selected'));
  slotEl.classList.add('selected');
  document.getElementById('selectedDateInput').value = `${dateStr} ${time}:00`;
}

document.getElementById('appointmentForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const errorElement = document.getElementById('formError');
  errorElement.style.display = 'none';

  if (!document.getElementById('doctorId').value ||
      !document.getElementById('patientName').value ||
      !document.getElementById('selectedDateInput').value) {
    errorElement.textContent = 'Please fill in all required fields and select a time slot.';
    errorElement.style.display = 'block';
    return;
  }

  const form = document.getElementById('appointmentForm');
  const formData = new FormData(form);

  fetch('../doctor/fetch_appointments.php', {
    method: 'POST',
    body: formData
  })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        showToast('Appointment booked successfully!');
        const modal = bootstrap.Modal.getInstance(document.getElementById('bookAppointmentModal'));
        modal.hide();
        document.getElementById('appointmentForm').reset();
        selectedDate = null;
        selectedTime = null;
        renderCalendar();
      } else {
        errorElement.textContent = result.error || 'Failed to book appointment.';
        errorElement.style.display = 'block';
      }
    })
    .catch(() => {
      errorElement.textContent = 'Failed to book appointment.';
      errorElement.style.display = 'block';
    });
});
</script>
</body>
</html>