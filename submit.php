<?php
header('Content-Type: application/json');

$host = 'localhost';
$db   = 'database';
$user = 'username';
$pass = 'password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$operator = trim($_POST['operator'] ?? '');
$currentStatus = trim($_POST['current_status'] ?? '');
$location = trim($_POST['location'] ?? '');
$equipment = trim($_POST['equipment'] ?? '');
$status = trim($_POST['status'] ?? '');

$days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
$dayStatuses = [];
foreach ($days as $day) {
    $dayStatuses[$day] = trim($_POST[$day . '_status'] ?? '');
}

$downtime = isset($_POST['downtime']) ? 1 : 0;
$plannedStart = $_POST['planned_downtime_start'] ?? null;
$plannedEnd = $_POST['planned_downtime_end'] ?? null;
$unplannedStart = $_POST['unplanned_downtime_start'] ?? null;
$unplannedEnd = $_POST['unplanned_downtime_end'] ?? null;

if ($operator === '' || $currentStatus === '' || $location === '' || $equipment === '' || $status === '') {
    echo json_encode(['success' => false, 'message' => 'Operator, current status, location, equipment and inspection are required.']);
    exit;
}

if ($downtime) {
    if ($plannedStart === '' || $plannedEnd === '' || $unplannedStart === '' || $unplannedEnd === '') {
        echo json_encode(['success' => false, 'message' => 'All downtime fields are required when downtime is enabled.']);
        exit;
    }
}

$sql = "INSERT INTO equipment_status_form (
            operator, current_status, location, equipment, status,
            monday_status, tuesday_status, wednesday_status, thursday_status, friday_status, saturday_status, sunday_status,
            downtime, planned_downtime_start, planned_downtime_end, unplanned_downtime_start, unplanned_downtime_end,
            created_at
        ) VALUES (
            :operator, :current_status, :location, :equipment, :status,
            :monday, :tuesday, :wednesday, :thursday, :friday, :saturday, :sunday,
            :downtime, :planned_start, :planned_end, :unplanned_start, :unplanned_end,
            NOW()
        )";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':operator' => $operator,
        ':current_status' => $currentStatus,
        ':location' => $location,
        ':equipment' => $equipment,
        ':status' => $status,
        ':monday' => $dayStatuses['monday'],
        ':tuesday' => $dayStatuses['tuesday'],
        ':wednesday' => $dayStatuses['wednesday'],
        ':thursday' => $dayStatuses['thursday'],
        ':friday' => $dayStatuses['friday'],
        ':saturday' => $dayStatuses['saturday'],
        ':sunday' => $dayStatuses['sunday'],
        ':downtime' => $downtime,
        ':planned_start' => $plannedStart,
        ':planned_end' => $plannedEnd,
        ':unplanned_start' => $unplannedStart,
        ':unplanned_end' => $unplannedEnd,
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
