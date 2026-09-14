<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging out - ScholaSys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
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
        .logout-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1.5rem;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 90%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: slideDown 0.6s ease-out;
            z-index: 10;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translate(-50%, -80px);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }
        .spinner {
            width: 60px;
            height: 60px;
            margin: 20px auto;
            border: 6px solid #e9ecef;
            border-top: 6px solid #4e73df;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .logout-icon {
            font-size: 3rem;
            color: #dc3545;
            margin-bottom: 1rem;
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <!-- Sliding diagonal backgrounds -->
    <div class="bg"></div>
    <div class="bg bg2"></div>
    <div class="bg bg3"></div>

    <div class="logout-card">
        <i class="fas fa-sign-out-alt logout-icon"></i>
        <h3>You have been logged out</h3>
        <p>Thank you for using ScholaSys.</p>
        <div class="spinner"></div>
        <p class="mt-3">Redirecting to login page...</p>
        <a href="/scholasys/public/login.php" class="btn btn-primary mt-2">Click here if not redirected</a>
    </div>

    <script>
        setTimeout(function() {
            window.location.href = "/scholasys/public/login.php";
        }, 2000);
    </script>
</body>
</html>