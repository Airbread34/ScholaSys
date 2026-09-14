<?php
require_once '../includes/config.php';
requireLogin();
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT graduate_id FROM employment_history WHERE id = ?");
    $stmt->execute([$id]);
    $grad_id = $stmt->fetchColumn();
    if ($grad_id) {
        $del = $pdo->prepare("DELETE FROM employment_history WHERE id = ?");
        $del->execute([$id]);
        header("Location: employment.php?id=$grad_id");
        exit;
    }
}
header('Location: graduates.php');
exit;