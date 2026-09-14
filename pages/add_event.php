<?php
require_once '../includes/config.php';
requireLogin();

if (!in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $date = $_POST['event_date'];
    $loc = trim($_POST['location']);
    $capacity = !empty($_POST['capacity']) ? $_POST['capacity'] : null;

    if (empty($title) || empty($date)) {
        $error = "Title and date are required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, location, capacity) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $desc, $date, $loc, $capacity]);
        $success = "Event added successfully.";
    }
}

include '../includes/header.php';
?>

<h2>Add Event</h2>
<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<form method="post">
    <div class="mb-3">
        <label>Title</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
    </div>
    <div class="mb-3">
        <label>Event Date</label>
        <input type="date" name="event_date" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Location</label>
        <input type="text" name="location" class="form-control">
    </div>
    <div class="mb-3">
        <label>Capacity (leave blank for unlimited)</label>
        <input type="number" name="capacity" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Save Event</button>
    <a href="events.php" class="btn btn-secondary">Cancel</a>
</form>

<?php include '../includes/footer.php'; ?>