<?php
require_once '../includes/config.php';
requireLogin();

$results = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = isset($_POST['fields']) ? $_POST['fields'] : [];
    $sel = [];
    if (in_array('student_id', $fields)) $sel[] = 'g.student_id';
    if (in_array('name', $fields)) $sel[] = "CONCAT(g.first_name, ' ', g.last_name) AS name";
    if (in_array('program', $fields)) $sel[] = 'g.program';
    if (in_array('year', $fields)) $sel[] = 'g.graduation_year';
    if (in_array('employer', $fields)) $sel[] = 'e.employer_name';
    if (in_array('job_title', $fields)) $sel[] = 'e.job_title';
    if (in_array('salary', $fields)) $sel[] = 'e.monthly_salary';
    if (empty($sel)) $sel = ['*'];
    $sql = "SELECT " . implode(', ', $sel) . " FROM graduates g LEFT JOIN employment e ON g.id = e.graduate_id WHERE 1=1";
    $params = [];
    if (!empty($_POST['program_filter'])) {
        $sql .= " AND g.program = :program";
        $params['program'] = $_POST['program_filter'];
    }
    if (!empty($_POST['year_filter'])) {
        $sql .= " AND g.graduation_year = :year";
        $params['year'] = $_POST['year_filter'];
    }
    if (!empty($_POST['employment_filter'])) {
        if ($_POST['employment_filter'] == 'employed') $sql .= " AND e.is_employed = 1";
        elseif ($_POST['employment_filter'] == 'unemployed') $sql .= " AND (e.is_employed = 0 OR e.is_employed IS NULL)";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}

include '../includes/header.php';
?>

<h2>Custom Report Builder</h2>
<form method="post" class="row g-3">
    <div class="col-md-6">
        <label>Select Fields</label>
        <div class="form-check"><input type="checkbox" name="fields[]" value="student_id"> Student ID</div>
        <div class="form-check"><input type="checkbox" name="fields[]" value="name"> Full Name</div>
        <div class="form-check"><input type="checkbox" name="fields[]" value="program"> Program</div>
        <div class="form-check"><input type="checkbox" name="fields[]" value="year"> Graduation Year</div>
        <div class="form-check"><input type="checkbox" name="fields[]" value="employer"> Employer</div>
        <div class="form-check"><input type="checkbox" name="fields[]" value="job_title"> Job Title</div>
        <div class="form-check"><input type="checkbox" name="fields[]" value="salary"> Monthly Salary</div>
    </div>
    <div class="col-md-6">
        <label>Filters</label>
        <div class="mb-2">
            <label>Program</label>
            <select name="program_filter" class="form-select">
                <option value="">Any</option>
                <?php
                $progs = $pdo->query("SELECT DISTINCT program FROM graduates ORDER BY program")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($progs as $p): ?>
                    <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-2">
            <label>Graduation Year</label>
            <select name="year_filter" class="form-select">
                <option value="">Any</option>
                <?php
                $years = $pdo->query("SELECT DISTINCT graduation_year FROM graduates ORDER BY graduation_year DESC")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($years as $y): ?>
                    <option value="<?= $y ?>"><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-2">
            <label>Employment Status</label>
            <select name="employment_filter" class="form-select">
                <option value="">Any</option>
                <option value="employed">Employed</option>
                <option value="unemployed">Unemployed</option>
            </select>
        </div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Generate Report</button>
    </div>
</form>

<?php if ($results !== null): ?>
    <hr>
    <h3>Results</h3>
    <?php if (count($results) == 0): ?>
        <p>No records found.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <?php foreach (array_keys($results[0]) as $col): ?>
                            <th><?= ucfirst(str_replace('_', ' ', $col)) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <?php foreach ($row as $val): ?>
                                <td><?= htmlspecialchars($val) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a href="export_graduates.php?<?= http_build_query($_POST) ?>" class="btn btn-success">Export CSV</a>
    <?php endif; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>