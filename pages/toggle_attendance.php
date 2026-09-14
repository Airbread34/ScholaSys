<?php
require_once '../includes/config.php';
requireLogin();

if (!in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: dashboard.php');
    exit;
}

$reg_id = $_GET['reg_id'] ?? 0;
$event_id = $_GET['event_id'] ?? 0;

if ($reg_id) {
    $stmt = $pdo->prepare("UPDATE event_registrations SET attended = NOT attended WHERE id = ?");
    $stmt->execute([$reg_id]);
}
header("Location: event_registrations.php?event_id=$event_id");
exit;