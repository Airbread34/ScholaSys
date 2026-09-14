<?php
$page_title = 'Dashboard';
require_once '../includes/config.php';
requireLogin();

// Fetch key metrics
$totalGrads = $pdo->query("SELECT COUNT(*) FROM graduates")->fetchColumn();
$employed = $pdo->query("SELECT COUNT(*) FROM employment WHERE is_employed = 1")->fetchColumn();
$employmentRate = ($totalGrads > 0) ? round(($employed / $totalGrads) * 100, 2) : 0;
$avgSalary = $pdo->query("SELECT AVG(monthly_salary) FROM employment WHERE is_employed = 1 AND monthly_salary IS NOT NULL")->fetchColumn();
$avgSalary = $avgSalary ? number_format($avgSalary, 2) : 'N/A';
$surveyCount = $pdo->query("SELECT COUNT(DISTINCT graduate_id) FROM tracer_surveys")->fetchColumn();
$surveyRate = ($totalGrads > 0) ? round(($surveyCount / $totalGrads) * 100, 2) : 0;

// Employment trend
$trendSql = "SELECT g.graduation_year, COUNT(g.id) AS total, SUM(e.is_employed) AS employed
             FROM graduates g LEFT JOIN employment e ON g.id = e.graduate_id
             GROUP BY g.graduation_year ORDER BY g.graduation_year ASC";
$trendData = $pdo->query($trendSql)->fetchAll();
$years = []; $trendRates = [];
foreach ($trendData as $row) {
    $years[] = $row['graduation_year'];
    $trendRates[] = ($row['total'] > 0) ? round(($row['employed']/$row['total'])*100,2) : 0;
}

// Salary by program
$salaryData = $pdo->query("SELECT g.program, AVG(e.monthly_salary) as avg_salary
    FROM graduates g JOIN employment e ON g.id = e.graduate_id
    WHERE e.is_employed = 1 AND e.monthly_salary IS NOT NULL GROUP BY g.program")->fetchAll();

// Industry distribution
$industryData = $pdo->query("SELECT CASE 
    WHEN employer_name LIKE '%school%' OR employer_name LIKE '%university%' THEN 'Education'
    WHEN employer_name LIKE '%hospital%' OR employer_name LIKE '%medical%' THEN 'Healthcare'
    WHEN employer_name LIKE '%bank%' OR employer_name LIKE '%finance%' THEN 'Finance'
    WHEN employer_name LIKE '%tech%' OR employer_name LIKE '%software%' THEN 'Technology'
    ELSE 'Other' END as industry, COUNT(*) as count
    FROM employment WHERE is_employed = 1 AND employer_name IS NOT NULL GROUP BY industry")->fetchAll();

// Recent graduates (with full name including middle initial)
$recentGrads = $pdo->query("SELECT * FROM graduates ORDER BY id DESC LIMIT 5")->fetchAll();

include '../includes/header.php';
?>

<style>
    .gradient-card {
        border-radius: 1rem;
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        color: white;
    }
    .gradient-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 2rem rgba(0,0,0,0.1);
    }
    .bg-gradient-primary { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); }
    .bg-gradient-info { background: linear-gradient(135deg, #36b9cc 0%, #258391 100%); }
    .bg-gradient-warning { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); }
    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.3;
        position: absolute;
        right: 1rem;
        top: 1rem;
    }
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    .recent-photo {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 50%;
        border: 1px solid #dee2e6;
    }
    .recent-photo-placeholder {
        font-size: 2rem;
        color: #adb5bd;
    }
</style>

<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card gradient-card bg-gradient-primary">
            <div class="card-body position-relative">
                <i class="fas fa-user-graduate stat-icon"></i>
                <h5 class="card-title">Total Graduates</h5>
                <h2 class="display-5"><?= $totalGrads ?></h2>
                <small>All time</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card gradient-card bg-gradient-success">
            <div class="card-body position-relative">
                <i class="fas fa-briefcase stat-icon"></i>
                <h5 class="card-title">Employed</h5>
                <h2 class="display-5"><?= $employed ?></h2>
                <small><?= $employmentRate ?>% of total</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card gradient-card bg-gradient-info">
            <div class="card-body position-relative">
                <i class="fas fa-dollar-sign stat-icon"></i>
                <h5 class="card-title">Avg Monthly Salary</h5>
                <h2 class="display-5">₱<?= $avgSalary ?></h2>
                <small>Employed graduates</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card gradient-card bg-gradient-warning">
            <div class="card-body position-relative">
                <i class="fas fa-poll stat-icon"></i>
                <h5 class="card-title">Survey Response</h5>
                <h2 class="display-5"><?= $surveyRate ?>%</h2>
                <small><?= $surveyCount ?> respondents</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2"></i>Employment Trend</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Average Salary by Program</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="salaryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Industry Distribution</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="industryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-building me-2"></i>Quick Reports</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <a href="reports.php" class="btn btn-outline-primary w-100"><i class="fas fa-table"></i> Employment by Program</a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="pdf_report.php" target="_blank" class="btn btn-outline-danger w-100"><i class="fas fa-file-pdf"></i> PDF Report</a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="custom_report.php" class="btn btn-outline-success w-100"><i class="fas fa-chart-simple"></i> Custom Report</a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="export_graduates.php" class="btn btn-outline-secondary w-100"><i class="fas fa-file-excel"></i> Export All</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-clock me-2"></i>Recently Added Graduates</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Program</th>
                                <th>Year</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentGrads as $g): ?>
                            <tr>
                                <td>
                                    <?php if ($g['profile_image']): ?>
                                        <img src="/scholasys/assets/uploads/<?= $g['profile_image'] ?>" class="recent-photo" alt="Photo">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle recent-photo-placeholder"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($g['student_id']) ?></td>
                                <td><?= htmlspecialchars($g['first_name'] . ' ' . ($g['middle_name'] ? $g['middle_name'][0] . '. ' : '') . $g['last_name']) ?></td>
                                <td><?= htmlspecialchars($g['program']) ?></td>
                                <td><?= $g['graduation_year'] ?></td>
                                <td>
                                    <a href="edit_graduate.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="employment.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-outline-info">Employment</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($years) ?>,
            datasets: [{
                label: 'Employment Rate (%)',
                data: <?= json_encode($trendRates) ?>,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78,115,223,0.1)',
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#224abe',
                pointBorderColor: '#fff',
                pointRadius: 4
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true, max: 100 } } }
    });

    new Chart(document.getElementById('salaryChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($salaryData, 'program')) ?>,
            datasets: [{
                label: 'Average Monthly Salary (PHP)',
                data: <?= json_encode(array_column($salaryData, 'avg_salary')) ?>,
                backgroundColor: '#1cc88a',
                borderRadius: 8
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { tooltip: { callbacks: { label: ctx => '₱' + ctx.raw.toLocaleString() } } } }
    });

    new Chart(document.getElementById('industryChart'), {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($industryData, 'industry')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($industryData, 'count')) ?>,
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
    });
</script>

<?php include '../includes/footer.php'; ?>