<?php
require_once 'includes/config.php';
require_once 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// SMTP settings – UPDATE WITH YOUR REAL CREDENTIALS
$smtpHost = 'smtp.gmail.com';
$smtpPort = 587;
$smtpUser = 'grefielkent@gmail.com';
$smtpPass = 'mkxm ttse dwtg zngr';   
$fromEmail = 'grefielkent@gmail.com';
$fromName = 'ScholaSys Alumni';

$currentYear = date('Y');
$annYears = [1,5,10,20,30,40];
foreach ($annYears as $ann) {
    $targetYear = $currentYear - $ann;
    $sql = "SELECT g.id, g.first_name, g.last_name, g.email
            FROM graduates g
            LEFT JOIN anniversary_reminders ar ON g.id = ar.graduate_id AND ar.anniversary_year = ?
            WHERE g.graduation_year = ? AND ar.id IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ann, $targetYear]);
    $grads = $stmt->fetchAll();

    foreach ($grads as $g) {
        $subject = "Happy {$ann}‑Year Graduation Anniversary!";
        $body = "Dear {$g['first_name']},\n\nCongratulations on your {$ann}‑year graduation anniversary! We're proud of your achievements.\n\nStay connected with ScholaSys.\n\n-- ScholaSys Team";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $smtpPort;
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($g['email'], $g['first_name'].' '.$g['last_name']);
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();

            $ins = $pdo->prepare("INSERT INTO anniversary_reminders (graduate_id, anniversary_year) VALUES (?, ?)");
            $ins->execute([$g['id'], $ann]);
            echo "Anniversary email sent to {$g['email']} ({$ann} years)\n";
        } catch (Exception $e) {
            error_log("Anniversary mail failed: " . $e->getMessage());
        }
    }
}