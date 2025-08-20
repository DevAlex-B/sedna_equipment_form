<?php
header('Content-Type: application/json');

$host = 'dw.digirockinnovations.com';
$db   = 'sedna';
$user = 'alex';
$pass = 'yHf7jK@3Lm!1';
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

// Ray casting algorithm for checking if point is inside polygon
function pointInPolygon(array $point, array $polygon): bool {
    $y = $point[0]; // latitude
    $x = $point[1]; // longitude
    $inside = false;
    $n = count($polygon);
    for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
        $yi = $polygon[$i][0];
        $xi = $polygon[$i][1];
        $yj = $polygon[$j][0];
        $xj = $polygon[$j][1];
        $intersect = (($yi > $y) != ($yj > $y)) &&
                     ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
        if ($intersect) {
            $inside = !$inside;
        }
    }
    return $inside;
}

function randomPointInPolygon(array $polygon): array {
    $lats = array_column($polygon, 0);
    $lngs = array_column($polygon, 1);
    $minLat = min($lats);
    $maxLat = max($lats);
    $minLng = min($lngs);
    $maxLng = max($lngs);

    do {
        $lat = $minLat + lcg_value() * ($maxLat - $minLat);
        $lng = $minLng + lcg_value() * ($maxLng - $minLng);
    } while (!pointInPolygon([$lat, $lng], $polygon));

    return [$lat, $lng];
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

// Fetch polygon for selected location and generate random coordinate
$geoStmt = $pdo->prepare('SELECT coordinates FROM geofences WHERE name = :name LIMIT 1');
$geoStmt->execute([':name' => $location]);
$geoRow = $geoStmt->fetch();
if (!$geoRow) {
    echo json_encode(['success' => false, 'message' => 'Selected location not found.']);
    exit;
}

$polygon = json_decode($geoRow['coordinates'], true);
if (!is_array($polygon)) {
    echo json_encode(['success' => false, 'message' => 'Invalid geofence data.']);
    exit;
}
$randomPoint = randomPointInPolygon($polygon);
$coordinate = $randomPoint[0] . ',' . $randomPoint[1];

$sql = "INSERT INTO equipment_status_form (
            operator, current_status, location, equipment, status,
            monday_status, tuesday_status, wednesday_status, thursday_status, friday_status, saturday_status, sunday_status,
            downtime, planned_downtime_start, planned_downtime_end, unplanned_downtime_start, unplanned_downtime_end,
            coordinate,
            created_at
        ) VALUES (
            :operator, :current_status, :location, :equipment, :status,
            :monday, :tuesday, :wednesday, :thursday, :friday, :saturday, :sunday,
            :downtime, :planned_start, :planned_end, :unplanned_start, :unplanned_end,
            :coordinate,
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
        ':coordinate' => $coordinate,
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
