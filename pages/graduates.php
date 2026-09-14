<?php
$page_title = 'Graduates';
require_once '../includes/config.php';
requireLogin();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$program = isset($_GET['program']) ? $_GET['program'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';
$employment_status = isset($_GET['employment_status']) ? $_GET['employment_status'] : '';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT g.*, e.is_employed FROM graduates g LEFT JOIN employment e ON g.id = e.graduate_id WHERE 1=1";
$countSql = "SELECT COUNT(*) FROM graduates g WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (g.first_name LIKE :search OR g.last_name LIKE :search OR g.student_id LIKE :search)";
    $countSql .= " AND (first_name LIKE :search OR last_name LIKE :search OR student_id LIKE :search)";
    $params['search'] = "%$search%";
}
if ($program !== '') {
    $sql .= " AND g.program = :program";
    $countSql .= " AND program = :program";
    $params['program'] = $program;
}
if ($year !== '') {
    $sql .= " AND g.graduation_year = :year";
    $countSql .= " AND graduation_year = :year";
    $params['year'] = $year;
}
if ($employment_status !== '') {
    if ($employment_status == 'employed') {
        $sql .= " AND e.is_employed = 1";
        $countSql = "SELECT COUNT(*) FROM graduates g INNER JOIN employment e ON g.id = e.graduate_id WHERE e.is_employed = 1";
        $countParams = [];
        if ($search !== '') $countParams['search'] = "%$search%";
        if ($program !== '') $countParams['program'] = $program;
        if ($year !== '') $countParams['year'] = $year;
    } elseif ($employment_status == 'unemployed') {
        $sql .= " AND (e.is_employed = 0 OR e.is_employed IS NULL)";
        $countSql = "SELECT COUNT(*) FROM graduates g LEFT JOIN employment e ON g.id = e.graduate_id WHERE (e.is_employed = 0 OR e.is_employed IS NULL)";
        $countParams = [];
        if ($search !== '') $countParams['search'] = "%$search%";
        if ($program !== '') $countParams['program'] = $program;
        if ($year !== '') $countParams['year'] = $year;
    }
}
if ($status_filter !== '') {
    $sql .= " AND g.status = :status";
    $countSql .= " AND status = :status";
    $params['status'] = $status_filter;
}

if (isset($countParams)) {
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($countParams);
} else {
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
}
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

$sql .= " ORDER BY g.id DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) $stmt->bindValue($key, $value);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$graduates = $stmt->fetchAll();

$programs = $pdo->query("SELECT DISTINCT program FROM graduates ORDER BY program")->fetchAll(PDO::FETCH_COLUMN);
$yearsList = $pdo->query("SELECT DISTINCT graduation_year FROM graduates ORDER BY graduation_year DESC")->fetchAll(PDO::FETCH_COLUMN);

include '../includes/header.php';
?>

<style>
    .graduate-photo {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .graduate-photo-placeholder {
        font-size: 2.5rem;
        color: #6c757d;
    }
</style>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success alert-dismissible"><?= htmlspecialchars($_SESSION['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible"><?= htmlspecialchars($_SESSION['error']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Filter Graduates</h3>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-3">
                <label>Search (Name/ID)</label>
                <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <label>Program</label>
                <select name="program" class="form-select">
                    <option value="">All</option>
                    <?php foreach ($programs as $p): ?>
                        <option value="<?= htmlspecialchars($p) ?>" <?= $program==$p?'selected':'' ?>><?= htmlspecialchars($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Year</label>
                <select name="year" class="form-select">
                    <option value="">All</option>
                    <?php foreach ($yearsList as $y): ?>
                        <option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Employment</label>
                <select name="employment_status" class="form-select">
                    <option value="">All</option>
                    <option value="employed" <?= $employment_status=='employed'?'selected':'' ?>>Employed</option>
                    <option value="unemployed" <?= $employment_status=='unemployed'?'selected':'' ?>>Unemployed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Status</label>
                <select name="status_filter" class="form-select">
                    <option value="">All</option>
                    <option value="active" <?= $status_filter=='active'?'selected':'' ?>>Active</option>
                    <option value="inactive" <?= $status_filter=='inactive'?'selected':'' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Apply</button>
                <a href="graduates.php" class="btn btn-secondary me-2">Reset</a>
                <a href="export_graduates.php?<?= http_build_query($_GET) ?>" class="btn btn-success">Export CSV</a>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between mt-3 mb-3">
    <a href="add_graduate.php" class="btn btn-success">Add Graduate</a>
    <span class="text-muted">Total: <?= $totalRecords ?> graduates</span>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Program</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th>Employed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($graduates)==0): ?>
                        <tr><td colspan="10">No graduates found</td></tr>
                    <?php else: foreach ($graduates as $g): ?>
                    <tr>
                        <td><?= $g['id'] ?></td>
                        <td><?= htmlspecialchars($g['student_id']) ?></td>
                        <td>
                            <?php if ($g['profile_image']): ?>
                                <img src="/scholasys/assets/uploads/<?= $g['profile_image'] ?>" class="graduate-photo" alt="Photo">
                            <?php else: ?>
                                <i class="fas fa-user-circle graduate-photo-placeholder"></i>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($g['first_name'] . ' ' . ($g['middle_name'] ? $g['middle_name'][0] . '. ' : '') . $g['last_name']) ?></td>
                        <td><?= htmlspecialchars($g['email']) ?></td>
                        <td><?= htmlspecialchars($g['program']) ?></td>
                        <td><?= $g['graduation_year'] ?></td>
                        <td>
                            <?php if ($g['status'] == 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?= isset($g['is_employed']) && $g['is_employed'] ? '<span class="badge bg-success">Employed</span>' : '<span class="badge bg-secondary">Unemployed</span>' ?></td>
                        <td>
                            <a href="edit_graduate.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="delete_graduate.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this graduate?')"><i class="fas fa-trash"></i> Delete</a>
                            <?php endif; ?>
                            <a href="employment.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-briefcase"></i> Employment</a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<nav><ul class="pagination justify-content-center">
    <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>">Previous</a></li>
    <?php for ($i=1; $i<=$totalPages; $i++): ?>
        <li class="page-item <?= $i==$page?'active':'' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"><?= $i ?></a></li>
    <?php endfor; ?>
    <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>">Next</a></li>
</ul></nav>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>