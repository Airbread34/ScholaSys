<?php
$page_title = 'Advanced Analytics';
require_once '../includes/config.php';
requireLogin();

// Get date range (year range)
$start_year = isset($_GET['start_year']) ? (int)$_GET['start_year'] : date('Y') - 10;
$end_year = isset($_GET['end_year']) ? (int)$_GET['end_year'] : date('Y');
if ($start_year > $end_year) list($start_year, $end_year) = [$end_year, $start_year];

$filter_sql = " AND g.graduation_year BETWEEN :start_year AND :end_year";

// 1. Employment trend (already had by year, but now filtered by range)
$trendSql = "SELECT g.graduation_year, COUNT(g.id) AS total, SUM(e.is_employed) AS employed
             FROM graduates g LEFT JOIN employment e ON g.id = e.graduate_id
             WHERE 1=1 $filter_sql
             GROUP BY g.graduation_year ORDER BY g.graduation_year ASC";
$stmt = $pdo->prepare($trendSql);
$stmt->execute(['start_year' => $start_year, 'end_year' => $end_year]);
$trendData = $stmt->fetchAll();
$years = []; $trendRates = [];
foreach ($trendData as $row) {
    $years[] = $row['graduation_year'];
    $trendRates[] = ($row['total'] > 0) ? round(($row['employed']/$row['total'])*100,2) : 0;
}

// 2. Average salary trend over years
$salaryTrendSql = "SELECT g.graduation_year, AVG(e.monthly_salary) as avg_salary
                   FROM graduates g JOIN employment e ON g.id = e.graduate_id
                   WHERE e.is_employed = 1 AND e.monthly_salary IS NOT NULL $filter_sql
                   GROUP BY g.graduation_year ORDER BY g.graduation_year ASC";
$stmt = $pdo->prepare($salaryTrendSql);
$stmt->execute(['start_year' => $start_year, 'end_year' => $end_year]);
$salaryTrend = $stmt->fetchAll();
$salaryYears = array_column($salaryTrend, 'graduation_year');
$salaryValues = array_column($salaryTrend, 'avg_salary');

// 3. Industry distribution over years (overall, but filtered by range)
$industrySql = "SELECT CASE 
    WHEN e.employer_name LIKE '%school%' OR e.employer_name LIKE '%university%' THEN 'Education'
    WHEN e.employer_name LIKE '%hospital%' OR e.employer_name LIKE '%medical%' THEN 'Healthcare'
    WHEN e.employer_name LIKE '%bank%' OR e.employer_name LIKE '%finance%' THEN 'Finance'
    WHEN e.employer_name LIKE '%tech%' OR e.employer_name LIKE '%software%' THEN 'Technology'
    ELSE 'Other' END as industry, COUNT(*) as count
    FROM employment e
    JOIN graduates g ON e.graduate_id = g.id
    WHERE e.is_employed = 1 AND e.employer_name IS NOT NULL $filter_sql
    GROUP BY industry";
$stmt = $pdo->prepare($industrySql);
$stmt->execute(['start_year' => $start_year, 'end_year' => $end_year]);
$industryData = $stmt->fetchAll();

// 4. Top employers (filtered)
$topSql = "SELECT e.employer_name, COUNT(*) as hires
          FROM employment e
          JOIN graduates g ON e.graduate_id = g.id
          WHERE e.is_employed = 1 AND e.employer_name IS NOT NULL AND e.employer_name != '' $filter_sql
          GROUP BY e.employer_name ORDER BY hires DESC LIMIT 5";
$stmt = $pdo->prepare($topSql);
$stmt->execute(['start_year' => $start_year, 'end_year' => $end_year]);
$topEmployers = $stmt->fetchAll();

include '../includes/header.php';
?>

<style>
    .chart-container { position: relative; height: 300px; width: 100%; margin-bottom: 1.5rem; }
    .filter-card { background: #f8f9fa; border-radius: 10px; padding: 1rem; margin-bottom: 1.5rem; }
</style>

<div class="filter-card">
    <h5><i class="fas fa-calendar-alt me-2"></i> Graduation Year Range</h5>
    <form method="get" class="row g-3">
        <div class="col-md-3">
            <label>From Year</label>
            <select name="start_year" class="form-select">
                <?php for ($y = date('Y')-20; $y <= date('Y'); $y++): ?>
                    <option value="<?= $y ?>" <?= $start_year == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>To Year</label>
            <select name="end_year" class="form-select">
                <?php for ($y = date('Y')-20; $y <= date('Y'); $y++): ?>
                    <option value="<?= $y ?>" <?= $end_year == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Apply Filter</button>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <a href="analytics.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Employment Trend (<?= $start_year ?> – <?= $end_year ?>)</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Average Salary Trend (<?= $start_year ?> – <?= $end_year ?>)</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="salaryTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Industry Distribution</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="industryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Top Employers</h5>
            </div>
            <div class="card-body">
                <?php if (empty($topEmployers)): ?>
                    <p class="text-muted">No employer data for this range.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($topEmployers as $emp): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($emp['employer_name']) ?>
                                <span class="badge bg-primary rounded-pill"><?= $emp['hires'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Employment trend line chart
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
                fill: true
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true, max: 100 } } }
    });

    // Salary trend line chart
    new Chart(document.getElementById('salaryTrendChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($salaryYears) ?>,
            datasets: [{
                label: 'Average Monthly Salary (PHP)',
                data: <?= json_encode($salaryValues) ?>,
                borderColor: '#1cc88a',
                backgroundColor: 'rgba(28,200,138,0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { tooltip: { callbacks: { label: ctx => '₱' + ctx.raw.toLocaleString() } } } }
    });

    // Industry pie chart
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