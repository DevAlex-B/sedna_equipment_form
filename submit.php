<?php
header('Content-Type: application/json');

$required = ['operator','equipment','status','monday_status','tuesday_status','wednesday_status','thursday_status','friday_status','saturday_status','sunday_status'];

foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success'=>false,'message'=>"Missing field: $field"]);
        exit;
    }
}

$downtime = isset($_POST['downtime']) ? 1 : 0;
$planned = !empty($_POST['planned_downtime']) ? $_POST['planned_downtime'] : null;
$unplanned = !empty($_POST['unplanned_downtime']) ? $_POST['unplanned_downtime'] : null;

try {
    $pdo = new PDO('mysql:host=localhost;dbname=YOUR_DB;charset=utf8mb4','USERNAME','PASSWORD');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('INSERT INTO equipment_status_form (operator,equipment,status,monday_status,tuesday_status,wednesday_status,thursday_status,friday_status,saturday_status,sunday_status,downtime,planned_downtime,unplanned_downtime,created_at) VALUES (:operator,:equipment,:status,:monday,:tuesday,:wednesday,:thursday,:friday,:saturday,:sunday,:downtime,:planned,:unplanned,NOW())');

    $stmt->execute([
        ':operator' => $_POST['operator'],
        ':equipment' => $_POST['equipment'],
        ':status' => $_POST['status'],
        ':monday' => $_POST['monday_status'],
        ':tuesday' => $_POST['tuesday_status'],
        ':wednesday' => $_POST['wednesday_status'],
        ':thursday' => $_POST['thursday_status'],
        ':friday' => $_POST['friday_status'],
        ':saturday' => $_POST['saturday_status'],
        ':sunday' => $_POST['sunday_status'],
        ':downtime' => $downtime,
        ':planned' => $planned,
        ':unplanned' => $unplanned
    ]);

    echo json_encode(['success'=>true,'message'=>'Form submitted successfully']);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>'Database error']);
}
