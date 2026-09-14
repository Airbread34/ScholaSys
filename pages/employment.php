<?php
require_once '../includes/config.php';
requireLogin();

$grad_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($grad_id == 0) {
    header('Location: graduates.php');
    exit;
}

$grad = $pdo->prepare("SELECT * FROM graduates WHERE id = ?");
$grad->execute([$grad_id]);
$grad = $grad->fetch();
if (!$grad) {
    header('Location: graduates.php');
    exit;
}

$empStmt = $pdo->prepare("SELECT * FROM employment WHERE graduate_id = ?");
$empStmt->execute([$grad_id]);
$emp = $empStmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_current'])) {
    $is_employed = isset($_POST['is_employed']) ? 1 : 0;
    $employer = $_POST['employer_name'] ?? null;
    $job_title = $_POST['job_title'] ?? null;
    $emp_type = $_POST['employment_type'] ?? null;
    $salary = $_POST['monthly_salary'] ?? null;
    $location = $_POST['work_location'] ?? null;

    if ($emp) {
        $stmt = $pdo->prepare("UPDATE employment SET is_employed=?, employer_name=?, job_title=?, employment_type=?, monthly_salary=?, work_location=? WHERE graduate_id=?");
        $stmt->execute([$is_employed, $employer, $job_title, $emp_type, $salary, $location, $grad_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO employment (graduate_id, is_employed, employer_name, job_title, employment_type, monthly_salary, work_location) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$grad_id, $is_employed, $employer, $job_title, $emp_type, $salary, $location]);
    }
    header("Location: employment.php?id=$grad_id");
    exit;
}

include '../includes/header.php';
?>

<h2>Employment Details for <?= htmlspecialchars($grad['first_name'] . ' ' . ($grad['middle_name'] ? $grad['middle_name'][0] . '. ' : '') . $grad['last_name']) ?></h2>

<form method="post" class="mb-4">
    <h3>Current Employment</h3>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="is_employed" id="employed" <?= ($emp && $emp['is_employed']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="employed">Currently Employed</label>
    </div>
    <div id="employmentFields" style="<?= ($emp && $emp['is_employed']) ? 'display:block' : 'display:none' ?>">
        <div class="mb-3"><label>Employer Name</label><input type="text" name="employer_name" class="form-control" value="<?= htmlspecialchars($emp['employer_name'] ?? '') ?>"></div>
        <div class="mb-3"><label>Job Title</label><input type="text" name="job_title" class="form-control" value="<?= htmlspecialchars($emp['job_title'] ?? '') ?>"></div>
        <div class="mb-3"><label>Employment Type</label>
            <select name="employment_type" class="form-control">
                <option value="">-- Select --</option>
                <option value="full-time" <?= (isset($emp['employment_type']) && $emp['employment_type']=='full-time') ? 'selected' : '' ?>>Full-time</option>
                <option value="part-time" <?= (isset($emp['employment_type']) && $emp['employment_type']=='part-time') ? 'selected' : '' ?>>Part-time</option>
                <option value="self-employed" <?= (isset($emp['employment_type']) && $emp['employment_type']=='self-employed') ? 'selected' : '' ?>>Self-employed</option>
                <option value="contractual" <?= (isset($emp['employment_type']) && $emp['employment_type']=='contractual') ? 'selected' : '' ?>>Contractual</option>
            </select>
        </div>
        <div class="mb-3"><label>Monthly Salary (PHP)</label><input type="number" step="0.01" name="monthly_salary" class="form-control" value="<?= htmlspecialchars($emp['monthly_salary'] ?? '') ?>"></div>
        <div class="mb-3"><label>Work Location</label><input type="text" name="work_location" class="form-control" value="<?= htmlspecialchars($emp['work_location'] ?? '') ?>"></div>
    </div>
    <button type="submit" name="save_current" class="btn btn-primary">Save Employment Info</button>
    <a href="graduates.php" class="btn btn-secondary">Back to Graduates</a>
</form>

<script>
    const employedCheck = document.getElementById('employed');
    const empFields = document.getElementById('employmentFields');
    employedCheck.addEventListener('change', function() {
        empFields.style.display = this.checked ? 'block' : 'none';
    });
</script>

<hr>
<h3>Employment History</h3>
<a href="add_employment_history.php?grad_id=<?= $grad_id ?>" class="btn btn-sm btn-primary mb-2">Add Job</a>
<table class="table table-sm">
    <thead><tr><th>Employer</th><th>Job Title</th><th>Start Date</th><th>End Date</th><th>Actions</th></tr></thead>
    <tbody>
    <?php
    $hist = $pdo->prepare("SELECT * FROM employment_history WHERE graduate_id = ? ORDER BY start_date DESC");
    $hist->execute([$grad_id]);
    while ($job = $hist->fetch()): ?>
         <tr>
            <td><?= htmlspecialchars($job['employer']) ?></td>
            <td><?= htmlspecialchars($job['job_title']) ?></td>
            <td><?= $job['start_date'] ?></td>
            <td><?= $job['end_date'] ?? 'Present' ?></td>
            <td>
                <a href="edit_employment_history.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <a href="delete_employment_history.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this job entry?')">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>