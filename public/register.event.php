<?php
require_once '../includes/config.php';
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();
if (!$event) die("Event not found.");

$error = $success = '';
$regStmt = $pdo->prepare("SELECT COUNT(*) FROM event_registrations WHERE event_id = ?");
$regStmt->execute([$event_id]);
$registered = $regStmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $gradStmt = $pdo->prepare("SELECT id FROM graduates WHERE student_id = ?");
    $gradStmt->execute([$student_id]);
    $grad = $gradStmt->fetch();
    if (!$grad) {
        $error = "Student ID not found.";
    } elseif ($event['capacity'] && $registered >= $event['capacity']) {
        $error = "Event is full.";
    } else {
        try {
            $ins = $pdo->prepare("INSERT INTO event_registrations (event_id, graduate_id) VALUES (?, ?)");
            $ins->execute([$event_id, $grad['id']]);
            $success = "Registration successful!";
            $regStmt->execute([$event_id]);
            $registered = $regStmt->fetchColumn();
        } catch (PDOException $e) {
            $error = ($e->errorInfo[1] == 1062) ? "Already registered." : "Registration failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register for Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5" style="max-width:600px">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4>Register for <?= htmlspecialchars($event['title']) ?></h4>
            </div>
            <div class="card-body">
                <p><strong>Date:</strong> <?= $event['event_date'] ?><br>
                <strong>Location:</strong> <?= htmlspecialchars($event['location']) ?><br>
                <?php if ($event['capacity']): ?>
                    <strong>Available slots:</strong> <?= max(0, $event['capacity'] - $registered) ?>
                <?php else: ?>Unlimited capacity<?php endif; ?></p>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                    <a href="register_event.php?event_id=<?= $event_id ?>" class="btn btn-primary">Register Another</a>
                    <a href="/scholasys/" class="btn btn-secondary">Home</a>
                <?php else: ?>
                    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                    <form method="post">
                        <div class="mb-3"><label>Student ID</label><input type="text" name="student_id" class="form-control" required></div>
                        <button type="submit" class="btn btn-primary">Register</button>
                        <a href="/scholasys/" class="btn btn-secondary">Cancel</a>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>