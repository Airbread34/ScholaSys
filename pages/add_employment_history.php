<?php
require_once '../includes/config.php';
requireLogin();

$grad_id = isset($_GET['grad_id']) ? (int)$_GET['grad_id'] : 0;
if ($grad_id == 0) die('Invalid graduate.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employer = $_POST['employer'];
    $job_title = $_POST['job_title'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'] ?: null;
    $salary = $_POST['salary'] ?: null;
    $is_current = isset($_POST['is_current']) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO employment_history (graduate_id, employer, job_title, start_date, end_date, salary, is_current) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$grad_id, $employer, $job_title, $start_date, $end_date, $salary, $is_current]);
    header("Location: employment.php?id=$grad_id");
    exit;
}
include '../includes/header.php';
?>
<h2>Add Employment History</h2>
<form method="post">
    <div class="mb-3"><label>Employer</label><input type="text" name="employer" class="form-control" required></div>
    <div class="mb-3"><label>Job Title</label><input type="text" name="job_title" class="form-control" required></div>
    <div class="mb-3"><label>Start Date</label><input type="date" name="start_date" class="form-control" required></div>
    <div class="mb-3"><label>End Date</label><input type="date" name="end_date" class="form-control"></div>
    <div class="mb-3"><label>Salary (PHP)</label><input type="number" step="0.01" name="salary" class="form-control"></div>
    <div class="form-check mb-3"><input type="checkbox" name="is_current" class="form-check-input"><label class="form-check-label">Current Job</label></div>
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="employment.php?id=<?= $grad_id ?>" class="btn btn-secondary">Cancel</a>
</form>
<?php include '../includes/footer.php'; ?>