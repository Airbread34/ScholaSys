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


$months = [6,12,24,36];
foreach ($months as $m) {
    $sql = "SELECT g.id, g.first_name, g.last_name, g.email, g.survey_token
            FROM graduates g
            LEFT JOIN survey_reminders sr ON g.id = sr.graduate_id AND sr.months_after = ?
            WHERE sr.id IS NULL
            AND DATE_ADD(CONCAT(g.graduation_year, '-06-01'), INTERVAL ? MONTH) <= CURDATE()
            AND DATE_ADD(CONCAT(g.graduation_year, '-06-01'), INTERVAL ?+1 MONTH) > CURDATE()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$m, $m, $m]);
    $grads = $stmt->fetchAll();

    foreach ($grads as $g) {
        if (empty($g['survey_token'])) {
            $token = bin2hex(random_bytes(32));
            $upd = $pdo->prepare("UPDATE graduates SET survey_token = ? WHERE id = ?");
            $upd->execute([$token, $g['id']]);
            $g['survey_token'] = $token;
        }
        $link = "http://localhost/scholasys/public/survey.php?token=" . $g['survey_token'];
        $subject = "ScholaSys Alumni Survey – {$m} Months After Graduation";
        $body = "Dear {$g['first_name']},\n\nWe'd love to hear about your career progress. Please take a moment to update your information: $link\n\nThank you!";

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

            $ins = $pdo->prepare("INSERT INTO survey_reminders (graduate_id, months_after) VALUES (?, ?)");
            $ins->execute([$g['id'], $m]);
            echo "Sent to {$g['email']} ({$m} months)\n";
        } catch (Exception $e) {
            error_log("Survey mail failed: " . $e->getMessage());
        }
    }
}