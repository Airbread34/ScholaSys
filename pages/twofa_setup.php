<?php
require_once '../includes/config.php';
require_once '../vendor/autoload.php';
requireLogin();

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\QR\GoogleChartsQRCodeProvider;

// Use the default GoogleCharts provider (doesn't need extra packages)
$qrProvider = new GoogleChartsQRCodeProvider();
$tfa = new TwoFactorAuth($qrProvider);

$user = $pdo->prepare("SELECT twofa_secret, twofa_enabled FROM users WHERE id = ?");
$user->execute([$_SESSION['user_id']]);
$u = $user->fetch();
$secret = $u['twofa_secret'];
$enabled = $u['twofa_enabled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enable'])) {
        $code = $_POST['code'];
        if ($tfa->verifyCode($secret, $code)) {
            $update = $pdo->prepare("UPDATE users SET twofa_enabled = 1 WHERE id = ?");
            $update->execute([$_SESSION['user_id']]);
            $enabled = true;
            $success = "2FA enabled.";
        } else $error = "Invalid code.";
    } elseif (isset($_POST['disable'])) {
        $update = $pdo->prepare("UPDATE users SET twofa_enabled = 0 WHERE id = ?");
        $update->execute([$_SESSION['user_id']]);
        $enabled = false;
        $success = "2FA disabled.";
    } elseif (isset($_POST['generate'])) {
        $newSecret = $tfa->createSecret();
        $update = $pdo->prepare("UPDATE users SET twofa_secret = ? WHERE id = ?");
        $update->execute([$newSecret, $_SESSION['user_id']]);
        $secret = $newSecret;
        $enabled = false;
        $showQR = true;
    }
}

include '../includes/header.php';
?>
<h2>Two‑Factor Authentication</h2>
<?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if (!$secret): ?>
    <form method="post"><button type="submit" name="generate" class="btn btn-primary">Generate Secret</button></form>
<?php elseif (isset($showQR) || !$enabled): ?>
    <p>Scan this QR code with Google Authenticator:</p>
    <?php
    // Generate QR code using an external free API (works without extra packages)
    $qrData = "otpauth://totp/ScholaSys:" . urlencode($_SESSION['username']) . "?secret={$secret}&issuer=ScholaSys";
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrData);
    ?>
    <img src="<?= $qrUrl ?>" alt="QR Code" style="border:1px solid #ccc; padding:5px;">
    <p class="mt-2">If the QR code doesn't load, enter this secret manually:<br>
    <strong><?= $secret ?></strong></p>
    <form method="post" class="mt-3">
        <div class="mb-3"><label>Verification Code</label><input type="text" name="code" class="form-control" required></div>
        <button type="submit" name="enable" class="btn btn-success">Enable 2FA</button>
    </form>
<?php else: ?>
    <p>2FA is currently <strong>ENABLED</strong>.</p>
    <form method="post"><button type="submit" name="disable" class="btn btn-danger">Disable 2FA</button></form>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>