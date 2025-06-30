<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Consultation - eMedConnect</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="Consultation.css">
  <style>
    .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 999; }
    .overlay.active { display: flex; justify-content: center; align-items: center; }
    .form-modal { background: #fff; padding: 2rem; border-radius: 10px; max-width: 600px; width: 100%; position: relative; max-height: 90vh; overflow-y: auto; }
    .calendar-date { cursor: pointer; padding: 5px 10px; border-radius: 5px; margin: 2px; display: inline-block; background-color: #e9f7ef; }
    .calendar-date.active { background-color: #198754; color: #fff; }
    .calendar-date .remove-btn { margin-left: 6px; color: red; cursor: pointer; font-weight: bold; }
    .doctor-info-card { background: #f8f9fa; padding: 1rem; border-radius: 10px; margin-top: 1rem; display: none; }
    .selectable-specialization.selected { border: 2px solid #198754; border-radius: 10px; }
    .filter-dropdown { position: absolute; right: 0; top: 100%; background: white; padding: 1rem; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); z-index: 100; display: none; }
    .filter-dropdown.active { display: block; }
    .specialization-icon img { width: 50px; height: 50px; object-fit: cover; }
    .calendar-day { cursor: pointer; padding: 10px; border-radius: 5px; margin: 2px; display: inline-block; text-align: center; }
    .calendar-day.available { background-color: #e9f7ef; }
    .calendar-day.selected { background-color: #198754; color: #fff; }
    .calendar-day-header { font-weight: bold; text-align: center; padding: 5px; }
    .time-slot { cursor: pointer; padding: 8px 12px; border-radius: 5px; margin: 2px; display: inline-block; background-color: #e9f7ef; }
    .time-slot.selected { background-color: #198754; color: #fff; }
    .availability-indicator { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 8px; }
    .availability-indicator.available { background-color: #28a745; }
    .availability-indicator.unavailable { background-color: #dc3545; }
    @media (max-width: 600px) {
      .modal-dialog-scrollable .modal-content {
        max-height: 90vh;
        display: flex;
        flex-direction: column;
      }
      .modal-dialog-scrollable .modal-body {
        overflow-y: auto;
        flex: 1 1 auto;
        max-height: 60vh;
      }
      .modal-footer {
        position: sticky;
        bottom: 0;
        background: #fff;
        z-index: 2;
      }
      .form-modal {
        max-height: 90vh;
        overflow-y: auto;
      }
    }
  </style>
</head>
<body>
<div class="d-flex" style="min-height: 100vh;">
  <?php include_once __DIR__ . '/../SideBar/Sidebar.php'; ?>
  <div class="flex-grow-1 main-content p-3">

    <!-- Top Bar -->
    <div class="d-flex align-items-center mb-4 position-relative">
      <input type="text" class="form-control search-bar" placeholder="Search Here" id="searchInput">
      <button class="btn btn-light ms-2" id="filterBtn"><i class="bi bi-sliders"></i></button>
      <div class="filter-dropdown" id="filterDropdown">
        <div class="mb-2"><strong>Filter By:</strong></div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="Pediatrics" id="filterPediatrics" checked>
          <label class="form-check-label" for="filterPediatrics">Pediatrics</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="Neurology" id="filterNeurology" checked>
          <label class="form-check-label" for="filterNeurology">Neurology</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="Dermatology" id="filterDermatology" checked>
          <label class="form-check-label" for="filterDermatology">Dermatology</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="OBGYN" id="filterOBGYN" checked>
          <label class="form-check-label" for="filterOBGYN">OBGYN</label>
        </div>
        <button class="btn btn-sm btn-primary mt-2" id="applyFilters">Apply</button>
      </div>
      <button class="btn btn-light ms-2"><i class="bi bi-bell"></i></button>
    </div>

    <h5 class="fw-bold mb-2">Find specialization</h5>
    <div class="mb-2">Select:</div>

    <!-- Specializations -->
    <div class="specialization-grid p-4 mb-4">
      <div class="row g-4" id="specializationsContainer">
        <?php
        $specializations = [
          "Pediatrics", "Neurology", "Dermatology", "OBGYN",
          "Gastroenterology", "Cardiology", "ENT-HNS", "Ophthalmology"
        ];
        foreach ($specializations as $spec): ?>
          <div class="col-6 col-md-3 text-center specialization-item" data-specialization="<?= htmlspecialchars($spec) ?>">
            <div class="specialization-icon mx-auto mb-2 selectable-specialization"
                 data-specialization="<?= htmlspecialchars($spec) ?>"
                 onclick="selectSpecialization(this)">
              <img src="../Images/profile-user.png" class="img-fluid">
            </div>
            <div><?= $spec ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <button class="button btn-green w-100 py-2 mb-4 fw-bold" onclick="toggleForm()">Make an appointment</button>

    <!-- Appointment Form Modal -->
    <div id="appointmentOverlay" class="overlay">
      <div class="form-modal">
        <h5 id="selectedSpecializationText" class="mb-3"></h5>
        <form id="appointmentBookingForm">
          <div class="mb-3">
            <label for="specializationSelect" class="form-label">Specialization</label>
            <select id="specializationSelect" name="specialization" class="form-select" onchange="fetchDoctors(this.value)">
              <option value="">Select</option>
              <option value="Pediatrics">Pediatrics</option>
              <option value="Neurology">Neurology</option>
              <option value="Dermatology">Dermatology</option>
              <option value="OBGYN">OBGYN</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="doctorSelect" class="form-label">Choose Doctor</label>
            <select id="doctorSelect" name="doctor_id" class="form-select" onchange="showDoctorInfo(this.value)">
              <option value="">Select a doctor</option>
            </select>
          </div>
          <div id="doctorDetails" class="doctor-info-card">
            <div><strong id="docName"></strong></div>
            <div>Email: <span id="docEmail"></span></div>
            <div>Contact: <span id="docContact"></span></div>
            <div>Specialization: <span id="docSpecialization"></span></div>
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
              <div class="d-flex flex-wrap mb-2" id="calendarDayHeaders"></div>
              <div class="d-flex flex-wrap" id="calendarGrid"></div>
              <div class="mt-3" id="timeSlots"></div>
            </div>
            <input type="hidden" id="selectedDateInput" name="selected_date">
          </div>
          <div class="mb-3">
            <label for="patientName" class="form-label">Your Name</label>
            <input type="text" id="patientName" name="patient_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="notes" class="form-label">Additional Notes</label>
            <textarea id="notes" name="notes" class="form-control"></textarea>
          </div>
          <div id="formError" class="text-danger" style="display: none;"></div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary me-2">Submit</button>
            <button type="button" class="btn btn-secondary" onclick="toggleForm(true)">Close</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Confirm Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Appointment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="confirmMsg" style="max-height: 60vh; overflow-y: auto;"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="confirmBtn">Confirm</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Appointment Modal (same as Doctor.php) -->
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
                  <div class="d-flex flex-wrap mb-2" id="calendarDayHeaders"></div>
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

  </div>
  <?php include_once __DIR__ . '/../SideBar_Right/SidebarRight.php'; ?>
</div>

<!-- Scripts -->
<script>
let selectedSpecialization = null;
let availableDates = {};
let selectedDate = null;
let selectedTime = null;
let currentYear = 2025; // Set to current year (June 16, 2025)
let currentMonth = 6;   // Set to current month (June)

document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('filterBtn').addEventListener('click', function() {
    document.getElementById('filterDropdown').classList.toggle('active');
  });

  document.getElementById('applyFilters').addEventListener('click', function() {
    applyFilters();
    document.getElementById('filterDropdown').classList.remove('active');
  });

  document.getElementById('searchInput').addEventListener('input', function() {
    filterSpecializations();
  });

  document.getElementById('appointmentBookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (validateAppointmentForm()) {
      showConfirmModal();
    }
  });

  document.getElementById('prevMonthBtn').addEventListener('click', function() {
    changeMonth(-1);
  });
  document.getElementById('nextMonthBtn').addEventListener('click', function() {
    changeMonth(1);
  });

  document.getElementById('specializationSelect').addEventListener('change', function() {
    selectedSpecialization = this.value;
    document.querySelectorAll('.selectable-specialization').forEach(el => {
      el.classList.toggle('selected', el.getAttribute('data-specialization') === selectedSpecialization);
    });
    fetchDoctors(this.value);
  });

  document.getElementById('confirmBtn').addEventListener('click', function() {
    submitAppointment();
  });

  document.getElementById('openAppointmentModalBtn')?.addEventListener('click', function() {
    document.getElementById('appointmentForm').reset();
    document.getElementById('formError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('bookAppointmentModal')).show();
  });

  // Handle appointment form submission in bookAppointmentModal
  document.getElementById('appointmentForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    fetch('submit_appointment.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Appointment submitted successfully');
        this.reset();
        bootstrap.Modal.getInstance(document.getElementById('bookAppointmentModal')).hide();
      } else {
        document.getElementById('formError').textContent = data.message || 'Error submitting appointment';
        document.getElementById('formError').style.display = 'block';
      }
    })
    .catch(error => {
      console.error('Network Error:', error);
      document.getElementById('formError').textContent = 'Network error occurred. Please try again.';
      document.getElementById('formError').style.display = 'block';
    });
  });
});

function validateAppointmentForm() {
  const doctor = document.getElementById('doctorSelect').value;
  const dateTime = document.getElementById('selectedDateInput').value;
  const patientName = document.getElementById('patientName').value;

  const errorElement = document.getElementById('formError');
  errorElement.style.display = 'none';

  if (!doctor) {
    errorElement.textContent = 'Please select a doctor.';
    errorElement.style.display = 'block';
    return false;
  }
  if (!dateTime) {
    errorElement.textContent = 'Please select a date and time slot.';
    errorElement.style.display = 'block';
    return false;
  }
  if (!patientName) {
    errorElement.textContent = 'Please enter your name.';
    errorElement.style.display = 'block';
    return false;
  }
  
  if (!isPatientLoggedIn()) {
    errorElement.textContent = 'Please log in as a patient to book an appointment.';
    errorElement.style.display = 'block';
    return false;
  }
  
  return true;
}

function isPatientLoggedIn() {
  return true; // Placeholder
}

function applyFilters() {
  const activeFilters = [];
  document.querySelectorAll('#filterDropdown input[type="checkbox"]:checked').forEach(checkbox => {
    activeFilters.push(checkbox.value);
  });
  document.querySelectorAll('.specialization-item').forEach(item => {
    const spec = item.getAttribute('data-specialization');
    item.style.display = activeFilters.includes(spec) ? 'block' : 'none';
  });
}

function filterSpecializations() {
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();
  document.querySelectorAll('.specialization-item').forEach(item => {
    const spec = item.getAttribute('data-specialization').toLowerCase();
    item.style.display = spec.includes(searchTerm) ? 'block' : 'none';
  });
}

function selectSpecialization(el) {
  document.querySelectorAll('.selectable-specialization').forEach(i => i.classList.remove('selected'));
  el.classList.add('selected');
  selectedSpecialization = el.getAttribute('data-specialization');
  document.getElementById('specializationSelect').value = selectedSpecialization;
  fetchDoctors(selectedSpecialization);
}

function toggleForm(close = false) {
  const overlay = document.getElementById('appointmentOverlay');
  if (close) {
    overlay.classList.remove('active');
    document.getElementById('doctorDetails').style.display = 'none';
    document.getElementById('calendarContainer').style.display = 'none';
    return;
  }
  if (!selectedSpecialization) {
    alert('Please select a specialization.');
    return;
  }
  document.getElementById('selectedSpecializationText').textContent = selectedSpecialization;
  overlay.classList.add('active');
  fetchDoctors(selectedSpecialization);
}

async function fetchDoctors(specialization) {
  if (!specialization) {
    document.getElementById('doctorSelect').innerHTML = '<option value="">Select a doctor</option>';
    document.getElementById('doctorDetails').style.display = 'none';
    document.getElementById('calendarContainer').style.display = 'none';
    return;
  }
  try {
    const response = await fetch(`get_doctors.php?specialization=${encodeURIComponent(specialization)}`);
    if (!response.ok) throw new Error('Failed to fetch doctors');
    const doctors = await response.json();
    const doctorSelect = document.getElementById('doctorSelect');
    doctorSelect.innerHTML = '<option value="">Select a doctor</option>';

    for (const doctor of doctors) {
      const availabilityResponse = await fetch(`get_available_dates.php?MedicId=${doctor.MedicID}&year=${currentYear}&month=${currentMonth}`);
      const availabilityData = await availabilityResponse.json();
      const isAvailable = availabilityData.slots && Object.keys(availabilityData.slots).length > 0;
      const option = document.createElement('option');
      option.value = doctor.MedicID;
      option.innerHTML = `<span class="availability-indicator ${isAvailable ? 'available' : 'unavailable'}"></span>${doctor.Username}`;
      doctorSelect.appendChild(option);
    }

    if (doctors.length > 0) {
      doctorSelect.value = doctors[0].MedicID;
      showDoctorInfo(doctors[0].MedicID);
    } else {
      document.getElementById('doctorDetails').style.display = 'none';
      document.getElementById('calendarContainer').style.display = 'none';
    }
  } catch (error) {
    console.error('Error fetching doctors:', error);
    document.getElementById('formError').textContent = 'Error loading doctors';
    document.getElementById('formError').style.display = 'block';
  }
}

async function showDoctorInfo(doctorId) {
  if (!doctorId) {
    document.getElementById('doctorDetails').style.display = 'none';
    document.getElementById('calendarContainer').style.display = 'none';
    return;
  }

  try {
    const doctorResponse = await fetch(`get_doctor_info.php?doctor_id=${encodeURIComponent(doctorId)}`);
    if (!doctorResponse.ok) throw new Error('Failed to fetch doctor info');
    const doctor = await doctorResponse.json();
    if (doctor.error || !doctor) {
      throw new Error(doctor.error || 'No doctor data returned');
    }

    document.getElementById('doctorDetails').style.display = 'block';
    document.getElementById('docName').textContent = `Dr. ${doctor.Username || 'N/A'}`;
    document.getElementById('docEmail').textContent = doctor.Email || 'N/A';
    document.getElementById('docContact').textContent = doctor.ContactNumber || 'N/A';
    document.getElementById('docSpecialization').textContent = doctor.Specialization || 'N/A';

    const datesResponse = await fetch(`get_available_dates.php?MedicId=${doctorId}&year=${currentYear}&month=${currentMonth}`);
    if (!datesResponse.ok) throw new Error('Failed to fetch available dates');
    const data = await datesResponse.json();
    if (data.error || !data.slots) {
      throw new Error(data.error || 'No availability data returned');
    }

    availableDates = data.slots || {};
    currentYear = data.year || currentYear;
    currentMonth = data.month || currentMonth;

    document.getElementById('calendarContainer').style.display = 'block';
    renderCalendar();
  } catch (error) {
    console.error('Error:', error);
    document.getElementById('doctorDetails').style.display = 'block';
    document.getElementById('docName').textContent = 'Error loading doctor';
    document.getElementById('docEmail').textContent = 'N/A';
    document.getElementById('docContact').textContent = 'N/A';
    document.getElementById('docSpecialization').textContent = 'N/A';
    document.getElementById('calendarGrid').innerHTML = `<p>Error: ${error.message}</p>`;
    document.getElementById('formError').textContent = error.message;
    document.getElementById('formError').style.display = 'block';
  }
}

function renderCalendar() {
  const calendarGrid = document.getElementById('calendarGrid');
  const dayHeaders = document.getElementById('calendarDayHeaders');
  const monthYear = document.getElementById('calendarMonthYear');
  calendarGrid.innerHTML = '';
  dayHeaders.innerHTML = '';
  monthYear.textContent = '';

  const dateObj = new Date(currentYear, currentMonth - 1, 1);
  monthYear.textContent = dateObj.toLocaleString('default', { month: 'long', year: 'numeric' });

  // Add day headers (Mon, Tue, etc.)
  const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  daysOfWeek.forEach(day => {
    const dayHeader = document.createElement('div');
    dayHeader.className = 'calendar-day-header';
    dayHeader.style.width = 'calc(100% / 7)';
    dayHeader.textContent = day;
    dayHeaders.appendChild(dayHeader);
  });

  const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
  const firstDayOfWeek = dateObj.getDay();
  for (let i = 0; i < firstDayOfWeek; i++) {
    calendarGrid.appendChild(document.createElement('div'));
  }
  for (let d = 1; d <= daysInMonth; d++) {
    const dayStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    const dayEl = document.createElement('div');
    dayEl.className = 'calendar-day';
    dayEl.style.width = 'calc(100% / 7)';
    dayEl.style.position = 'relative';
    dayEl.textContent = d;

    // Add indicator for available days
    if (availableDates[dayStr] && availableDates[dayStr].length > 0) {
      dayEl.classList.add('available');
      // Add a light green dot indicator
      const indicator = document.createElement('span');
      indicator.style.position = 'absolute';
      indicator.style.top = '6px';
      indicator.style.right = '8px';
      indicator.style.width = '10px';
      indicator.style.height = '10px';
      indicator.style.borderRadius = '50%';
      indicator.style.background = '#90ee90'; // light green
      indicator.title = 'Available';
      dayEl.appendChild(indicator);

      dayEl.onclick = () => selectDate(dayStr, dayEl);
    }
    if (selectedDate === dayStr) dayEl.classList.add('selected');
    calendarGrid.appendChild(dayEl);
  }
  if (Object.keys(availableDates).length === 0) {
    calendarGrid.innerHTML = '<div class="text-muted">No available dates for this month</div>';
    document.getElementById('timeSlots').innerHTML = '';
  }

  // Add legend below the calendar if not already present
  let legend = document.getElementById('calendarLegend');
  if (!legend) {
    legend = document.createElement('div');
    legend.id = 'calendarLegend';
    legend.innerHTML = `
      <span style="display:inline-block;width:12px;height:12px;background:#90ee90;border-radius:50%;margin-right:6px;vertical-align:middle;"></span>
      <span style="vertical-align:middle;">Available</span>
    `;
    calendarGrid.parentNode.appendChild(legend);
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
    slot.className = 'time-slot';
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

function showConfirmModal() {
  const doctorId = document.getElementById('doctorSelect').value;
  const dateTime = document.getElementById('selectedDateInput').value;
  const patientName = document.getElementById('patientName').value;
  const notes = document.getElementById('notes').value;
  const doctorName = document.getElementById('docName').textContent;

  const [dateStr, time] = dateTime.split(' ');
  const confirmMsg = document.getElementById('confirmMsg');
  confirmMsg.innerHTML = `
    <div><strong>Doctor:</strong> ${doctorName}</div>
    <div><strong>Date:</strong> ${new Date(dateStr).toLocaleDateString()}</div>
    <div><strong>Time:</strong> ${time} <span style="text-transform:lowercase;">${time >= '12:00' ? 'PM' : 'AM'}</span></div>
    <div><strong>Patient:</strong> ${patientName}</div>
    <div><strong>Notes:</strong> ${notes || 'None'}</div>
    <div class="text-muted mt-2" style="font-size:0.9em;">The timing is in your local time zone (${Intl.DateTimeFormat().resolvedOptions().timeZone})</div>
  `;
  const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
  confirmModal.show();
}

function changeMonth(delta) {
  currentMonth += delta;
  if (currentMonth < 1) {
    currentMonth = 12;
    currentYear--;
  } else if (currentMonth > 12) {
    currentMonth = 1;
    currentYear++;
  }
  const doctorId = document.getElementById('doctorSelect').value;
  if (doctorId) {
    showDoctorInfo(doctorId);
  }
}

async function submitAppointment() {
  const form = document.getElementById('appointmentBookingForm');
  const formData = new FormData(form);
  const errorElement = document.getElementById('formError');

  try {
    const response = await fetch('../doctor/fetch_appointments.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success) {
      alert('Appointment booked successfully!');
      toggleForm(true);
      form.reset();
      errorElement.style.display = 'none';
      document.getElementById('doctorDetails').style.display = 'none';
      document.getElementById('calendarContainer').style.display = 'none';
      bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
    } else {
      errorElement.textContent = result.error || 'Error booking appointment';
      errorElement.style.display = 'block';
      bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
    }
  } catch (error) {
    console.error('Error:', error);
    errorElement.textContent = 'Network error. Please try again.';
    errorElement.style.display = 'block';
    bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
  }
}

function handleAppointmentAction(appointmentId, action) {
  fetch('update_appointment_status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `appointment_id=${appointmentId}&action=${action}`
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert(`Appointment ${action}ed successfully.`);
        location.reload();
      } else {
        alert(`Error: ${data.error}`);
      }
    })
    .catch(error => alert('Error: ' + error));
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>