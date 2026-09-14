<?php
require_once '../includes/config.php';
requireLogin();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$program = isset($_GET['program']) ? $_GET['program'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';
$employment_status = isset($_GET['employment_status']) ? $_GET['employment_status'] : '';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

$sql = "SELECT g.id, g.student_id, g.first_name, g.middle_name, g.last_name, g.email, g.contact_number, 
               g.program, g.graduation_year, g.status,
               COALESCE(e.is_employed, 0) AS is_employed,
               e.employer_name, e.job_title, e.employment_type, e.monthly_salary, e.work_location
        FROM graduates g
        LEFT JOIN employment e ON g.id = e.graduate_id
        WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (g.first_name LIKE :search OR g.last_name LIKE :search OR g.student_id LIKE :search)";
    $params['search'] = "%$search%";
}
if ($program !== '') {
    $sql .= " AND g.program = :program";
    $params['program'] = $program;
}
if ($year !== '') {
    $sql .= " AND g.graduation_year = :year";
    $params['year'] = $year;
}
if ($employment_status !== '') {
    if ($employment_status == 'employed') {
        $sql .= " AND e.is_employed = 1";
    } elseif ($employment_status == 'unemployed') {
        $sql .= " AND (e.is_employed = 0 OR e.is_employed IS NULL)";
    }
}
if ($status_filter !== '') {
    $sql .= " AND g.status = :status";
    $params['status'] = $status_filter;
}
$sql .= " ORDER BY g.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$graduates = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="graduates_export_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($output, [
    'ID','Student ID','First Name','Middle Name','Last Name','Email','Contact Number',
    'Program','Graduation Year','Status','Employed','Employer Name','Job Title',
    'Employment Type','Monthly Salary','Work Location'
]);

foreach ($graduates as $grad) {
    fputcsv($output, [
        $grad['id'],
        $grad['student_id'],
        $grad['first_name'],
        $grad['middle_name'],
        $grad['last_name'],
        $grad['email'],
        $grad['contact_number'],
        $grad['program'],
        $grad['graduation_year'],
        $grad['status'],
        $grad['is_employed'] ? 'Yes' : 'No',
        $grad['employer_name'],
        $grad['job_title'],
        $grad['employment_type'],
        $grad['monthly_salary'],
        $grad['work_location']
    ]);
}
fclose($output);
exit;