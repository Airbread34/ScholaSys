<?php
require_once '../includes/config.php';
requireLogin();

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Upload failed.";
    } else {
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // skip header
        $inserted = 0;
        $skipped = 0;
        while (($row = fgetcsv($handle)) !== FALSE) {
            // Expect columns: student_id, first_name, last_name, email, contact_number, program, graduation_year
            if (count($row) < 7) continue;
            list($student_id, $first_name, $last_name, $email, $contact, $program, $year) = $row;
            $year = (int)$year;
            // Check duplicate
            $check = $pdo->prepare("SELECT id FROM graduates WHERE student_id = ?");
            $check->execute([$student_id]);
            if (!$check->fetch()) {
                $token = bin2hex(random_bytes(32));
                $stmt = $pdo->prepare("INSERT INTO graduates (student_id, first_name, last_name, email, contact_number, program, graduation_year, survey_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$student_id, $first_name, $last_name, $email, $contact, $program, $year, $token]);
                $inserted++;
            } else {
                $skipped++;
            }
        }
        fclose($handle);
        $success = "Imported $inserted new graduates. Skipped $skipped duplicates.";
    }
}

include '../includes/header.php';
?>

<h2>Import Graduates from CSV</h2>
<p>CSV format: student_id, first_name, last_name, email, contact_number, program, graduation_year</p>
<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
    <div class="mb-3">
        <label>CSV File</label>
        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
    </div>
    <button type="submit" class="btn btn-primary">Import</button>
    <a href="graduates.php" class="btn btn-secondary">Cancel</a>
</form>

<?php include '../includes/footer.php'; ?>