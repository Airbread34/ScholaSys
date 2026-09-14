<?php
$page_title = 'Reports';
require_once '../includes/config.php';
requireLogin();

// Fetch data
$programStats = $pdo->query("
    SELECT g.program, 
           COUNT(g.id) AS total_grads,
           SUM(e.is_employed) AS employed
    FROM graduates g
    LEFT JOIN employment e ON g.id = e.graduate_id
    GROUP BY g.program
")->fetchAll();

$topEmployers = $pdo->query("
    SELECT employer_name, COUNT(*) as hires
    FROM employment
    WHERE is_employed = 1 AND employer_name IS NOT NULL AND employer_name != ''
    GROUP BY employer_name
    ORDER BY hires DESC
    LIMIT 10
")->fetchAll();

include '../includes/header.php';
?>

<!-- DataTables CSS & JS + Export buttons -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Employment Rate by Program</h3>
    </div>
    <div class="card-body">
        <table id="programTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Program</th>
                    <th>Total Graduates</th>
                    <th>Employed</th>
                    <th>Rate (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($programStats as $stat): ?>
                <tr>
                    <td><?= htmlspecialchars($stat['program']) ?></td>
                    <td><?= $stat['total_grads'] ?></td>
                    <td><?= $stat['employed'] ?? 0 ?></td>
                    <td><?= $stat['total_grads'] > 0 ? round(($stat['employed']/$stat['total_grads'])*100, 2) : 0 ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">Top Employers</h3>
    </div>
    <div class="card-body">
        <table id="employerTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Employer</th>
                    <th>Hires</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topEmployers as $emp): ?>
                <tr>
                    <td><?= htmlspecialchars($emp['employer_name']) ?></td>
                    <td><?= $emp['hires'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#programTable').DataTable({
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            pageLength: 10,
            responsive: true
        });
        $('#employerTable').DataTable({
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            pageLength: 10,
            responsive: true
        });
    });
</script>

<?php include '../includes/footer.php'; ?>