<?php
session_start();
require_once '../includes/config.php';
require_once '../vendor/autoload.php';
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\QR\GoogleChartsQRCodeProvider;

if (!isset($_SESSION['2fa_user_id'])) {
    header('Location: login.php');
    exit;
}

$qrProvider = new GoogleChartsQRCodeProvider();
$tfa = new TwoFactorAuth($qrProvider);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $user = $pdo->prepare("SELECT twofa_secret FROM users WHERE id = ?");
    $user->execute([$_SESSION['2fa_user_id']]);
    $secret = $user->fetchColumn();
    if ($tfa->verifyCode($secret, $code)) {
        $_SESSION['user_id'] = $_SESSION['2fa_user_id'];
        $_SESSION['username'] = $_SESSION['2fa_username'];
        $_SESSION['role'] = $_SESSION['2fa_role'];
        unset($_SESSION['2fa_user_id'], $_SESSION['2fa_username'], $_SESSION['2fa_role']);
        header('Location: /scholasys/pages/dashboard.php');
        exit;
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>2FA Verification</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <div class="container mt-5" style="max-width:400px">
        <div class="card">
            <div class="card-header bg-primary text-white">Two‑Factor Authentication</div>
            <div class="card-body">
                <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                <form method="post">
                    <div class="mb-3"><label>Verification Code</label><input type="text" name="code" class="form-control" required></div>
                    <button type="submit" class="btn btn-primary">Verify</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>