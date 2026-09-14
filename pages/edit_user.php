<?php
$page_title = 'Edit User';
require_once '../includes/config.php';
requireLogin();

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    header('Location: users.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username']);
    $role = $_POST['role'];
    $newPassword = $_POST['password'];

    // Check if username already exists for another user
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $check->execute([$newUsername, $id]);
    if ($check->fetch()) {
        $error = "Username '$newUsername' is already taken.";
    } else {
        // Build update query
        if (!empty($newPassword)) {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
            $update->execute([$newUsername, $hashed, $role, $id]);
        } else {
            $update = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
            $update->execute([$newUsername, $role, $id]);
        }
        $success = "User updated successfully.";
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
    }
}

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit User: <?= htmlspecialchars($user['username']) ?></h3>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label>New Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-select">
                    <option value="staff" <?= $user['role'] == 'staff' ? 'selected' : '' ?>>Staff</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="users.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>