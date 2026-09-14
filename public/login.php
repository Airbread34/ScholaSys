<?php
require_once '../includes/config.php';
require_once '../includes/recaptcha_config.php';

if (isLoggedIn()) {
    header('Location: /scholasys/pages/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $recaptcha_response = $_POST['g-recaptcha-response'];

    $recaptcha_verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . RECAPTCHA_SECRET_KEY . "&response=" . $recaptcha_response);
    $recaptcha_result = json_decode($recaptcha_verify);

    if ($recaptcha_result->success && $recaptcha_result->score >= RECAPTCHA_SCORE_THRESHOLD && $recaptcha_result->action == 'login') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['twofa_enabled']) {
                $_SESSION['2fa_user_id'] = $user['id'];
                $_SESSION['2fa_username'] = $user['username'];
                $_SESSION['2fa_role'] = $user['role'];
                $_SESSION['2fa_profile_image'] = $user['profile_image'];
                header('Location: /scholasys/public/twofa_verify.php');
                exit;
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_image'] = $user['profile_image'];
                header('Location: /scholasys/pages/dashboard.php');
                exit;
            }
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Bot verification failed. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ScholaSys | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        body {
            font-family: 'Poppins', sans-serif;
        }
        /* Sliding diagonals background */
        .bg {
            animation: slide 3s ease-in-out infinite alternate;
            background-image: linear-gradient(-60deg, #6c3 50%, #09f 50%);
            bottom: 0;
            left: -50%;
            opacity: 0.5;
            position: fixed;
            right: -50%;
            top: 0;
            z-index: -2;
        }
        .bg2 {
            animation-direction: alternate-reverse;
            animation-duration: 4s;
        }
        .bg3 {
            animation-duration: 5s;
        }
        @keyframes slide {
            0% { transform: translateX(-25%); }
            100% { transform: translateX(25%); }
        }
        /* Card container */
        .login-container {
            width: 100%;
            max-width: 450px;
            animation: fadeInUp 0.8s ease-out;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate(-50%, -30px);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }
        .card {
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 40px rgba(0, 0, 0, 0.25);
        }
        .card-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            text-align: center;
            padding: 2rem 1rem;
            border-bottom: none;
        }
        .brand-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0px); }
        }
        .card-header h2 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .card-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 2rem;
            padding: 0.75rem 1rem;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78,115,223,0.25);
            transform: scale(1.02);
        }
        .input-group-text {
            background: transparent;
            border-right: none;
            border-radius: 2rem 0 0 2rem;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 2rem 2rem 0;
        }
        .btn-login {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            border-radius: 2rem;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78,115,223,0.4);
        }
        .btn-login:active {
            transform: translateY(1px);
        }
        .btn-login::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.4s, height 0.4s;
        }
        .btn-login:active::after {
            width: 100%;
            height: 100%;
        }
        .alert {
            border-radius: 2rem;
            border: none;
            font-size: 0.9rem;
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .footer-text {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.8rem;
            color: #6c757d;
        }
        .grecaptcha-badge { 
            visibility: hidden; /* Hides the reCAPTCHA badge (optional) */
        }
    </style>
</head>
<body>
    <!-- Sliding diagonals background -->
    <div class="bg"></div>
    <div class="bg bg2"></div>
    <div class="bg bg3"></div>

    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-graduation-cap brand-icon"></i>
                <h2>ScholaSys</h2>
                <p>Graduate Data Management System</p>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <form id="loginForm" method="post">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" name="username" placeholder="Enter your username" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-login" id="loginButton">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>
                </form>
                <div class="footer-text">
                    <p>© <?= date('Y') ?> ScholaSys. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://www.google.com/recaptcha/api.js?render=<?= RECAPTCHA_SITE_KEY ?>"></script>
    <script>
        const form = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            loginButton.disabled = true;
            loginButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Verifying...';

            grecaptcha.ready(function() {
                grecaptcha.execute('<?= RECAPTCHA_SITE_KEY ?>', {action: 'login'}).then(function(token) {
                    const tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = 'g-recaptcha-response';
                    tokenInput.value = token;
                    form.appendChild(tokenInput);
                    form.submit();
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>