<?php
require_once '../includes/config.php';
requireLogin();

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

if (isset($_GET['id']) && $_GET['id'] != $_SESSION['user_id']) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header('Location: users.php');
exit;