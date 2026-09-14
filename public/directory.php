<?php
require_once '../includes/config.php';

$isLoggedIn = isset($_SESSION['user_id']);
$search = isset($_GET['search']) ? $_GET['search'] : '';
$program = isset($_GET['program']) ? $_GET['program'] : '';

$sql = "SELECT g.id, g.first_name, g.middle_name, g.last_name, g.program, g.graduation_year, e.employer_name, e.job_title
        FROM graduates g
        LEFT JOIN employment e ON g.id = e.graduate_id AND e.is_employed = 1
        WHERE g.show_in_directory = 1";
$params = [];
if ($search) {
    $sql .= " AND (g.first_name LIKE :search OR g.last_name LIKE :search OR g.program LIKE :search)";
    $params['search'] = "%$search%";
}
if ($program) {
    $sql .= " AND g.program = :program";
    $params['program'] = $program;
}
$sql .= " ORDER BY g.graduation_year DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$alumni = $stmt->fetchAll();

$programs = $pdo->query("SELECT DISTINCT program FROM graduates WHERE show_in_directory = 1 ORDER BY program")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Alumni Directory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/scholasys/assets/css/style.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Alumni Directory</h1>
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name or program" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="program" class="form-select">
                    <option value="">All Programs</option>
                    <?php foreach ($programs as $p): ?>
                        <option value="<?= htmlspecialchars($p) ?>" <?= $program == $p ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
        <div class="row">
            <?php foreach ($alumni as $a): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($a['first_name'] . ' ' . ($a['middle_name'] ? $a['middle_name'][0] . '. ' : '') . $a['last_name']) ?></h5>
                            <p class="card-text">
                                <strong>Program:</strong> <?= htmlspecialchars($a['program']) ?><br>
                                <strong>Year:</strong> <?= $a['graduation_year'] ?><br>
                                <?php if ($a['employer_name']): ?>
                                    <strong>Currently at:</strong> <?= htmlspecialchars($a['employer_name']) ?><br>
                                    <strong>Role:</strong> <?= htmlspecialchars($a['job_title']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
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