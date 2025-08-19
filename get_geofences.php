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
    $stmt = $pdo->query('SELECT name FROM geofences');
    $names = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($names);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>
