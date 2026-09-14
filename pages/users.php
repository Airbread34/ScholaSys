<?php
require_once '../includes/config.php';
requireLogin();

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();

include '../includes/header.php';
?>

<h1>User Management</h1>
<a href="add_user.php" class="btn btn-primary mb-3"><i class="fas fa-user-plus"></i> Add New User</a>

<table class="table table-bordered">
    <thead>
        <tr><th>ID</th><th>Username</th><th>Role</th><th>Created</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= ucfirst($u['role']) ?></td>
            <td><?= $u['created_at'] ?></td>
            <td>
                <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                    <a href="delete_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>