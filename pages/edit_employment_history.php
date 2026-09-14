<?php
$page_title = 'Edit Employment History';
require_once '../includes/config.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM employment_history WHERE id = ?");
$stmt->execute([$id]);
$job = $stmt->fetch();
if (!$job) {
    header('Location: graduates.php');
    exit;
}
$grad_id = $job['graduate_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employer = $_POST['employer'];
    $job_title = $_POST['job_title'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'] ?: null;
    $salary = $_POST['salary'] ?: null;
    $is_current = isset($_POST['is_current']) ? 1 : 0;

    $update = $pdo->prepare("UPDATE employment_history SET employer=?, job_title=?, start_date=?, end_date=?, salary=?, is_current=? WHERE id=?");
    $update->execute([$employer, $job_title, $start_date, $end_date, $salary, $is_current, $id]);
    header("Location: employment.php?id=$grad_id");
    exit;
}

include '../includes/header.php';
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Employment History</h3>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="mb-3"><label>Employer</label><input type="text" name="employer" class="form-control" value="<?= htmlspecialchars($job['employer']) ?>" required></div>
            <div class="mb-3"><label>Job Title</label><input type="text" name="job_title" class="form-control" value="<?= htmlspecialchars($job['job_title']) ?>" required></div>
            <div class="mb-3"><label>Start Date</label><input type="date" name="start_date" class="form-control" value="<?= $job['start_date'] ?>" required></div>
            <div class="mb-3"><label>End Date</label><input type="date" name="end_date" class="form-control" value="<?= $job['end_date'] ?>"></div>
            <div class="mb-3"><label>Salary (PHP)</label><input type="number" step="0.01" name="salary" class="form-control" value="<?= $job['salary'] ?>"></div>
            <div class="form-check mb-3"><input type="checkbox" name="is_current" class="form-check-input" value="1" <?= $job['is_current'] ? 'checked' : '' ?>><label class="form-check-label">Current Job</label></div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="employment.php?id=<?= $grad_id ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>