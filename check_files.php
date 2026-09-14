<?php
$files = [
    'includes/config.php', 'includes/header.php', 'includes/footer.php',
    'public/index.php', 'public/login.php', 'public/logout.php', 'public/survey.php', 'public/directory.php', 'public/register_event.php', 'public/events_list.php', 'public/twofa_verify.php',
    'pages/dashboard.php', 'pages/graduates.php', 'pages/add_graduate.php', 'pages/edit_graduate.php', 'pages/delete_graduate.php',
    'pages/employment.php', 'pages/add_employment_history.php', 'pages/edit_employment_history.php', 'pages/delete_employment_history.php',
    'pages/reports.php', 'pages/pdf_report.php', 'pages/change_password.php', 'pages/export_graduates.php',
    'pages/users.php', 'pages/add_user.php', 'pages/edit_user.php', 'pages/delete_user.php',
    'pages/events.php', 'pages/add_event.php', 'pages/edit_event.php', 'pages/delete_event.php',
    'pages/event_registrations.php', 'pages/toggle_attendance.php',
    'pages/announcements.php', 'pages/add_announcement.php', 'pages/edit_announcement.php', 'pages/delete_announcement.php',
    'pages/import_graduates.php', 'pages/custom_report.php', 'pages/twofa_setup.php', 'pages/newsletter.php', 'pages/analytics.php',
    'assets/css/style.css', 'assets/js/main.js',
    'send_survey_reminders.php', 'send_anniversary_reminders.php', 'scholasys.sql', 'composer.json'
];
echo "<h2>ScholaSys File Check</h2><ul>";
foreach ($files as $f) {
    $path = __DIR__ . '/' . $f;
    echo "<li>" . $f . " – " . (file_exists($path) ? "✅" : "❌") . "</li>";
}
echo "</ul>";
echo "<p>If any file is missing, tell me its name and I'll provide the code.</p>";