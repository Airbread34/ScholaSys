<?php
require_once '../includes/config.php';

// Optional: restrict access to localhost only for security
$allowed_ips = ['127.0.0.1', '::1'];
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    die("Access denied. This script can only be run from localhost.");
}

echo "<h2>Password Reset Tool</h2>";

// Default: reset admin password to 'admin123'
$target_username = 'admin';
$new_password = 'admin123';

$hashed = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
if ($stmt->execute([$hashed, $target_username])) {
    echo "<p style='color:green'>✅ Password for user '<strong>{$target_username}</strong>' has been reset to: <strong>{$new_password}</strong></p>";
} else {
    echo "<p style='color:red'>❌ Failed to update password. Make sure the user '{$target_username}' exists.</p>";
}

// Show all users (optional)
echo "<h3>Existing Users</h3>";
$users = $pdo->query("SELECT id, username, role FROM users")->fetchAll();
if ($users) {
    echo "<ul>";
    foreach ($users as $u) {
        echo "<li>Username: <strong>" . htmlspecialchars($u['username']) . "</strong> (Role: {$u['role']})</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No users found.</p>";
}

echo "<hr>";
echo "<p style='color:red'><strong>IMPORTANT:</strong> Delete this file immediately after use!</p>";