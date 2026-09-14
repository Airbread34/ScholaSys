<?php
$page_title = 'Add Graduate';
require_once '../includes/config.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact_number']);
    $program = trim($_POST['program']);
    $year = (int)$_POST['graduation_year'];
    $status = $_POST['status'];

    $checkStmt = $pdo->prepare("SELECT id FROM graduates WHERE student_id = ?");
    $checkStmt->execute([$student_id]);
    if ($checkStmt->fetch()) {
        $error = "Student ID '$student_id' already exists. Please use a unique ID.";
    } else {
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("INSERT INTO graduates (student_id, first_name, middle_name, last_name, email, contact_number, program, graduation_year, status, survey_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$student_id, $first_name, $middle_name, $last_name, $email, $contact, $program, $year, $status, $token])) {
            $success = "Graduate added successfully! Redirecting...";
            header('Refresh: 2; url=graduates.php');
        } else {
            $error = "Failed to add graduate. Please try again.";
        }
    }
}

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add New Graduate</h3>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Student ID</label>
                    <input type="text" name="student_id" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Program</label>
                    <input type="text" name="program" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Graduation Year</label>
                    <input type="number" name="graduation_year" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="graduates.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>