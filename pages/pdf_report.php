<?php
require_once '../includes/config.php';
requireLogin();

require_once '../vendor/autoload.php'; // adjust path if needed

use Dompdf\Dompdf;

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

// Build HTML
$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ScholaSys Employment Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h1 { color: #1e3c72; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #1e3c72; color: white; }
        .footer { font-size: 10px; text-align: center; margin-top: 30px; color: #666; }
    </style>
</head>
<body>
    <h1>ScholaSys Employment Report</h1>
    <p>Generated on ' . date('Y-m-d H:i:s') . '</p>

    <h2>Employment Rate by Program</h2>
    <table>
        <thead>
            <tr><th>Program</th><th>Total Graduates</th><th>Employed</th><th>Rate (%)</th></tr>
        </thead>
        <tbody>';

foreach ($programStats as $stat) {
    $rate = ($stat['total_grads'] > 0) ? round(($stat['employed']/$stat['total_grads'])*100, 2) : 0;
    $html .= '<tr><td>' . htmlspecialchars($stat['program']) . '</td><td>' . $stat['total_grads'] . '</td><td>' . ($stat['employed'] ?? 0) . '</td><td>' . $rate . '%</td></tr>';
}

$html .= '</tbody>
    </table>

    <h2>Top Employers</h2>
    <table>
        <thead>
            <tr><th>Employer</th><th>Hires</th></tr>
        </thead>
        <tbody>';

foreach ($topEmployers as $emp) {
    $html .= '<tr><td>' . htmlspecialchars($emp['employer_name']) . '</td><td>' . $emp['hires'] . '</td></tr>';
}

$html .= '</tbody>
    </table>

    <div class="footer">ScholaSys Graduate Data Management System</div>
</body>
</html>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("scholasys_report.pdf", ["Attachment" => false]);
