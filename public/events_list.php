<?php
require_once '../includes/config.php';

$isLoggedIn = isset($_SESSION['user_id']);
$stmt = $pdo->prepare("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC");
$stmt->execute();
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upcoming Events - ScholaSys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/scholasys/assets/css/style.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Upcoming Events</h1>
        <?php if (empty($events)): ?>
            <p class="text-muted">No upcoming events at the moment.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($events as $event): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                                <p class="card-text">
                                    <strong><i class="fas fa-calendar-alt"></i> Date:</strong> <?= $event['event_date'] ?><br>
                                    <strong><i class="fas fa-map-marker-alt"></i> Location:</strong> <?= htmlspecialchars($event['location']) ?><br>
                                    <?= nl2br(htmlspecialchars($event['description'])) ?>
                                </p>
                                <a href="register_event.php?event_id=<?= $event['id'] ?>" class="btn btn-primary">Register Now</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="mt-3">
            <?php if ($isLoggedIn): ?>
                <a href="/scholasys/pages/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            <?php else: ?>
                <a href="/scholasys/" class="btn btn-secondary">Back to Home</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>