<?php
require_once '../includes/config.php';
if (isLoggedIn()) {
    header('Location: /scholasys/pages/dashboard.php');
} else {
    header('Location: /scholasys/public/login.php');
}
exit;