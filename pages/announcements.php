<?php
require_once '../includes/config.php';
requireLogin();

if (!in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC");
$announcements = $stmt->fetchAll();

include '../includes/header.php';
?>

<h1>Manage Announcements</h1>
<a href="add_announcement.php" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Add Announcement</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Content</th>
            <th>Created</th>
            <th>Expires</th>
            <th>Actions</th>
        </thead>
    <tbody>
        <?php foreach ($announcements as $a): ?>
        <tr>
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['title']) ?></td>
            <td><?= nl2br(htmlspecialchars(substr($a['content'], 0, 100))) ?>...</td>
            <td><?= $a['created_at'] ?></td>
            <td><?= $a['expires_at'] ?? 'Never' ?></td>
            <td>
                <a href="edit_announcement.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <a href="delete_announcement.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this announcement?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>