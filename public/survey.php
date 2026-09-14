<?php
require_once '../includes/config.php';

$error = '';
$success = '';
$grad = null;

$token = isset($_GET['token']) ? $_GET['token'] : '';
if ($token) {
    $stmt = $pdo->prepare("SELECT * FROM graduates WHERE survey_token = ?");
    $stmt->execute([$token]);
    $grad = $stmt->fetch();
    if (!$grad) $error = "Invalid survey link.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($grad) {
        $grad_id = $grad['id'];
    } else {
        $student_id = $_POST['student_id'];
        $stmt = $pdo->prepare("SELECT id, graduation_year FROM graduates WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $g = $stmt->fetch();
        if (!$g) {
            $error = "Student ID not found.";
        } else {
            $grad_id = $g['id'];
            $gradYear = $g['graduation_year'];
        }
    }

    if (!isset($error) || empty($error)) {
        $is_employed = isset($_POST['is_employed']) ? 1 : 0;
        $employer = $_POST['employer_name'] ?? null;
        $job_title = $_POST['job_title'] ?? null;
        $emp_type = $_POST['employment_type'] ?? null;
        $salary = $_POST['monthly_salary'] ?? null;
        $location = $_POST['work_location'] ?? null;
        $additional_edu = $_POST['additional_education'] ?? null;
        $feedback = $_POST['feedback'] ?? null;

        $empStmt = $pdo->prepare("SELECT * FROM employment WHERE graduate_id = ?");
        $empStmt->execute([$grad_id]);
        $existing = $empStmt->fetch();
        if ($existing) {
            $upd = $pdo->prepare("UPDATE employment SET is_employed=?, employer_name=?, job_title=?, employment_type=?, monthly_salary=?, work_location=? WHERE graduate_id=?");
            $upd->execute([$is_employed, $employer, $job_title, $emp_type, $salary, $location, $grad_id]);
        } else {
            $ins = $pdo->prepare("INSERT INTO employment (graduate_id, is_employed, employer_name, job_title, employment_type, monthly_salary, work_location) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([$grad_id, $is_employed, $employer, $job_title, $emp_type, $salary, $location]);
        }

        if (!isset($gradYear)) {
            $gy = $pdo->prepare("SELECT graduation_year FROM graduates WHERE id = ?");
            $gy->execute([$grad_id]);
            $gradYear = $gy->fetchColumn();
        }
        $currentYear = date('Y');
        $currentMonth = date('m');
        $months = ($currentYear - $gradYear) * 12 + ($currentMonth - 6); // assume June graduation

        $survey = $pdo->prepare("INSERT INTO tracer_surveys (graduate_id, survey_date, months_after_graduation, additional_education, feedback) VALUES (?, CURDATE(), ?, ?, ?)");
        $survey->execute([$grad_id, $months, $additional_edu, $feedback]);

        $success = "Thank you! Your information has been recorded.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>ScholaSys Survey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/scholasys/assets/css/style.css">
</head>
<body>
    <div class="container mt-5" style="max-width:600px">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Graduate Tracer Survey</h4>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                    <a href="survey.php" class="btn btn-primary">Take Another Survey</a>
                <?php else: ?>
                    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                    <form method="post">
                        <?php if (!$token): ?>
                            <div class="mb-3">
                                <label>Student ID</label>
                                <input type="text" name="student_id" class="form-control" required>
                            </div>
                        <?php endif; ?>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_employed" id="employed">
                            <label class="form-check-label">Currently Employed</label>
                        </div>
                        <div id="empFields" style="display:none">
                            <div class="mb-3"><label>Employer</label><input type="text" name="employer_name" class="form-control"></div>
                            <div class="mb-3"><label>Job Title</label><input type="text" name="job_title" class="form-control"></div>
                            <div class="mb-3"><label>Employment Type</label>
                                <select name="employment_type" class="form-control">
                                    <option value="">-- Select --</option>
                                    <option value="full-time">Full-time</option>
                                    <option value="part-time">Part-time</option>
                                    <option value="self-employed">Self-employed</option>
                                    <option value="contractual">Contractual</option>
                                </select>
                            </div>
                            <div class="mb-3"><label>Monthly Salary (PHP)</label><input type="number" step="0.01" name="monthly_salary" class="form-control"></div>
                            <div class="mb-3"><label>Work Location</label><input type="text" name="work_location" class="form-control"></div>
                        </div>
                        <div class="mb-3"><label>Additional Education</label><textarea name="additional_education" class="form-control" rows="2"></textarea></div>
                        <div class="mb-3"><label>Feedback</label><textarea name="feedback" class="form-control" rows="3"></textarea></div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('employed').addEventListener('change', function() {
            document.getElementById('empFields').style.display = this.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>