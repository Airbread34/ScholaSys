<?php
require_once '../includes/config.php';

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();
if (!$event) {
    die("Event not found.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $stmt = $pdo->prepare("SELECT id FROM graduates WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $grad = $stmt->fetch();
    if (!$grad) {
        $error = "Student ID not found.";
    } else {
        // Check capacity
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM event_registrations WHERE event_id = ?");
        $countStmt->execute([$event_id]);
        $registered = $countStmt->fetchColumn();
        if ($event['capacity'] && $registered >= $event['capacity']) {
            $error = "Sorry, the event is full.";
        } else {
            try {
                $ins = $pdo->prepare("INSERT INTO event_registrations (event_id, graduate_id) VALUES (?, ?)");
                $ins->execute([$event_id, $grad['id']]);
                $success = "You have successfully registered for the event!";
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    $error = "You are already registered for this event.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Event - ScholaSys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/scholasys/assets/css/style.css">
</head>
<body>
    <div class="container mt-5" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Register for Event</h4>
            </div>
            <div class="card-body">
                <h5><?= htmlspecialchars($event['title']) ?></h5>
                <p><strong>Date:</strong> <?= $event['event_date'] ?><br>
                <strong>Location:</strong> <?= htmlspecialchars($event['location']) ?><br>
                <?php if ($event['capacity']): ?>
                <strong>Available slots:</strong> <?= max(0, $event['capacity'] - $registered) ?>
                <?php else: ?>
                <strong>Unlimited capacity</strong>
                <?php endif; ?>
                </p>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label>Your Student ID</label>
                            <input type="text" name="student_id" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Register</button>
                        <a href="/scholasys/" class="btn btn-secondary">Cancel</a>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>