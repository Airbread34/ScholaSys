<?php
require_once '../includes/config.php';
requireLogin();

if (!in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
$events = $stmt->fetchAll();

include '../includes/header.php';
?>

<h1>Events Management</h1>
<a href="add_event.php" class="btn btn-primary mb-3"><i class="fas fa-calendar-plus"></i> Add Event</a>

<table class="table table-bordered">
    <thead>
        <tr><th>ID</th><th>Title</th><th>Date</th><th>Location</th><th>Capacity</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php foreach ($events as $e): ?>
        <tr>
            <td><?= $e['id'] ?></td>
            <td><?= htmlspecialchars($e['title']) ?></td>
            <td><?= $e['event_date'] ?></td>
            <td><?= htmlspecialchars($e['location']) ?></td>
            <td><?= $e['capacity'] ?? 'Unlimited' ?></td>
            <td>
                <a href="edit_event.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <a href="delete_event.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this event?')">Delete</a>
                <a href="event_registrations.php?event_id=<?= $e['id'] ?>" class="btn btn-sm btn-info">Registrations</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>