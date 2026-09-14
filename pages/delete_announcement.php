<?php
require_once '../includes/config.php';
requireLogin();

if (!in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: dashboard.php');
    exit;
}

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header('Location: announcements.php');
exit;