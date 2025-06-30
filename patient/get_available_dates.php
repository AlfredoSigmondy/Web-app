<?php
// get_available_dates.php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli('127.0.0.1', 'root', '', 'emed');
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Get doctor_id, year, and month from GET request
$doctor_id = isset($_GET['MedicId']) ? (int)$_GET['MedicId'] : 0;
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');

if (!$doctor_id) {
    echo json_encode(['error' => 'Doctor ID required']);
    exit;
}

// Validate year and month
if ($year < 2025 || $month < 1 || $month > 12) {
    echo json_encode(['error' => 'Invalid year or month']);
    exit;
}

// Initialize response
$response = [
    'year' => $year,
    'month' => $month,
    'slots' => []
];

// Define date range for the specified month
$start_date = new DateTime("$year-$month-01");
$end_date = (clone $start_date)->modify('last day of this month');
$interval = new DateInterval('P1D');
$period = new DatePeriod($start_date, $interval, $end_date->modify('+1 day'));

// Check if doctor is approved
$query = "SELECT MedicID FROM doctors WHERE MedicID = ? AND Status = 'Approved'";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $stmt->close();
    echo json_encode(['error' => 'Doctor not found or not approved']);
    exit;
}
$stmt->close();

// Fetch regular availability
$query = "SELECT day_of_week, start_time, end_time, valid_from, valid_to 
          FROM doctor_availability 
          WHERE doctor_id = ? AND (valid_to IS NULL OR valid_to >= ?)";
$stmt = $conn->prepare($query);
$start_date_str = $start_date->format('Y-m-d');
$stmt->bind_param('is', $doctor_id, $start_date_str);
$stmt->execute();
$availability_result = $stmt->get_result();
$regular_availability = [];
while ($row = $availability_result->fetch_assoc()) {
    $regular_availability[$row['day_of_week']] = [
        'start_time' => $row['start_time'],
        'end_time' => $row['end_time'],
        'valid_from' => $row['valid_from'],
        'valid_to' => $row['valid_to']
    ];
}
$stmt->close();

// Fetch exceptions
$query = "SELECT exception_date, is_available, start_time, end_time 
          FROM availability_exceptions 
          WHERE doctor_id = ? AND exception_date BETWEEN ? AND ?";
$stmt = $conn->prepare($query);
$end_date_str = $end_date->format('Y-m-d');
$stmt->bind_param('iss', $doctor_id, $start_date_str, $end_date_str);
$stmt->execute();
$exceptions_result = $stmt->get_result();
$exceptions = [];
while ($row = $exceptions_result->fetch_assoc()) {
    $exceptions[$row['exception_date']] = [
        'is_available' => $row['is_available'],
        'start_time' => $row['start_time'],
        'end_time' => $row['end_time']
    ];
}
$stmt->close();

// Fetch booked appointments
$query = "SELECT appointment_date, end_time 
          FROM appointments 
          WHERE doctor_id = ? AND appointment_date BETWEEN ? AND ? 
          AND status NOT IN ('Cancelled', 'No-Show')";
$stmt = $conn->prepare($query);
$stmt->bind_param('iss', $doctor_id, $start_date_str, $end_date_str);
$stmt->execute();
$appointments_result = $stmt->get_result();
$booked_slots = [];
while ($row = $appointments_result->fetch_assoc()) {
    $booked_slots[] = [
        'start' => $row['appointment_date'],
        'end' => $row['end_time'] ? date('Y-m-d H:i:s', strtotime($row['appointment_date'] . ' ' . $row['end_time'])) : null
    ];
}
$stmt->close();

// Generate available slots
foreach ($period as $date) {
    $date_str = $date->format('Y-m-d');
    $day_of_week = (int)$date->format('w');

    // Skip dates before current date
    $current_date = new DateTime('2025-06-16 01:00:00');
    if ($date < $current_date) {
        continue;
    }

    // Check exceptions
    if (isset($exceptions[$date_str])) {
        if (!$exceptions[$date_str]['is_available']) {
            continue;
        }
        $start_time = $exceptions[$date_str]['start_time'];
        $end_time = $exceptions[$date_str]['end_time'];
    } elseif (isset($regular_availability[$day_of_week])) {
        $start_time = $regular_availability[$day_of_week]['start_time'];
        $end_time = $regular_availability[$day_of_week]['end_time'];
    } else {
        continue;
    }

    if (!$start_time || !$end_time) {
        continue;
    }

    // Generate 30-minute time slots
    $slots = [];
    $current_time = new DateTime("$date_str $start_time");
    $end_time_dt = new DateTime("$date_str $end_time");
    $slot_interval = new DateInterval('PT30M');

    while ($current_time < $end_time_dt) {
        $slot_start = $current_time->format('H:i');
        $slot_start_full = $current_time->format('Y-m-d H:i:s');
        $slot_end_dt = (clone $current_time)->add($slot_interval);

        // Check if slot is booked
        $is_booked = false;
        foreach ($booked_slots as $booked) {
            $booked_start = new DateTime($booked['start']);
            $booked_end = $booked['end'] ? new DateTime($booked['end']) : (clone $booked_start)->modify('+30 minutes');
            $slot_start_dt = new DateTime($slot_start_full);
            if ($slot_start_dt >= $booked_start && $slot_start_dt < $booked_end) {
                $is_booked = true;
                break;
            }
        }

        if (!$is_booked) {
            $slots[] = $slot_start;
        }
        $current_time->add($slot_interval);
    }

    if (!empty($slots)) {
        $response['slots'][$date_str] = $slots;
    }
}

$conn->close();
echo json_encode($response);
?>