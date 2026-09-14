<?php
require_once '../includes/config.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!password_verify($current, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new !== $confirm) {
        $error = "New password and confirmation do not match.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$hashed, $_SESSION['user_id']]);
        $success = "Password changed successfully!";
    }
}

include '../includes/header.php';
?>

<h2>Change Password</h2>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<form method="post">
    <div class="mb-3"><label>Current Password</label><input type="password" name="current_password" class="form-control" required></div>
    <div class="mb-3"><label>New Password</label><input type="password" name="new_password" class="form-control" required></div>
    <div class="mb-3"><label>Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
    <button type="submit" class="btn btn-primary">Change Password</button>
    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
</form>

<?php include '../includes/footer.php'; ?>