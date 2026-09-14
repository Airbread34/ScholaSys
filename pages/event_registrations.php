<?php
$page_title = 'Event Registrations';
require_once '../includes/config.php';
requireLogin();

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();
if (!$event) {
    header('Location: events.php');
    exit;
}

$registrations = $pdo->prepare("
    SELECT er.*, g.first_name, g.middle_name, g.last_name, g.email
    FROM event_registrations er
    JOIN graduates g ON er.graduate_id = g.id
    WHERE er.event_id = ?
    ORDER BY er.registered_at DESC
");
$registrations->execute([$event_id]);
$regs = $registrations->fetchAll();

include '../includes/header.php';
?>

<h1>Registrations for "<?= htmlspecialchars($event['title']) ?>"</h1>
<p><strong>Date:</strong> <?= $event['event_date'] ?> | <strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
<p><strong>Capacity:</strong> <?= $event['capacity'] ?? 'Unlimited' ?> | <strong>Registered:</strong> <?= count($regs) ?></p>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Graduate</th>
            <th>Email</th>
            <th>Registered At</th>
            <th>Attended</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($regs) == 0): ?>
            <tr><td colspan="5" class="text-center">No registrations yet.</td></tr>
        <?php else: ?>
            <?php foreach ($regs as $reg): ?>
            <tr>
                <td><?= htmlspecialchars($reg['first_name'] . ' ' . ($reg['middle_name'] ? $reg['middle_name'][0] . '. ' : '') . $reg['last_name']) ?></td>
                <td><?= htmlspecialchars($reg['email']) ?></td>
                <td><?= $reg['registered_at'] ?></td>
                <td><?= $reg['attended'] ? 'Yes' : 'No' ?></td>
                <td>
                    <a href="toggle_attendance.php?reg_id=<?= $reg['id'] ?>&event_id=<?= $event_id ?>" class="btn btn-sm btn-info">Toggle Attendance</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<a href="events.php" class="btn btn-secondary">Back to Events</a>

<?php include '../includes/footer.php'; ?>