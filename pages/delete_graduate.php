<?php
require_once '../includes/config.php';
requireLogin();

if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Only administrators can delete graduates.";
    header('Location: graduates.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $check = $pdo->prepare("SELECT id FROM graduates WHERE id = ?");
        $check->execute([$id]);
        if ($check->fetch()) {
            $stmt = $pdo->prepare("DELETE FROM graduates WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['message'] = "Graduate deleted successfully.";
        } else {
            $_SESSION['error'] = "Graduate not found.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "No graduate ID specified.";
}
header('Location: graduates.php');
exit;