<?php
$page_title = 'Activity Log';
require_once '../includes/config.php';
requireLogin();

// Only admin can view the log
if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

// Optional: filter by action
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

$sql = "SELECT * FROM activity_log";
$params = [];
if ($filter !== '') {
    $sql .= " WHERE action LIKE :filter";
    $params['filter'] = "%$filter%";
}
$sql .= " ORDER BY created_at DESC LIMIT 200";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Activity Log</h3>
        <form method="get" class="d-flex">
            <input type="text" name="filter" class="form-control form-control-sm me-2"
                   placeholder="Filter by action..." value="<?= htmlspecialchars($filter) ?>">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <a href="activity_log.php" class="btn btn-sm btn-secondary ms-2">Reset</a>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) == 0): ?>
                        <tr><td colspan="5" class="text-center p-3">No activity recorded yet.</td></tr>
                    <?php else: foreach ($logs as $log): ?>
                    <tr>
                        <td><?= $log['id'] ?></td>
                        <td>
                            <?php if ($log['username']): ?>
                                <i class="fas fa-user-circle"></i> <?= htmlspecialchars($log['username']) ?>
                            <?php else: ?>
                                <span class="text-muted">System</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-primary"><?= htmlspecialchars($log['action']) ?></span></td>
                        <td><?= htmlspecialchars($log['details']) ?></td>
                        <td><?= $log['created_at'] ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
