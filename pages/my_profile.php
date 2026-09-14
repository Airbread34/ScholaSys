<?php
$page_title = 'My Profile';
require_once '../includes/config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check->execute([$new_username, $user_id]);
        if ($check->fetch()) {
            $error = "Username '$new_username' is already taken.";
        } else {
            // Profile image upload
            $profile_image = $user['profile_image'];
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array(strtolower($ext), $allowed)) {
                    $error = "Only JPG, PNG, GIF images are allowed.";
                } else {
                    $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
                    $target = '../assets/uploads/' . $filename;
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
                        if ($profile_image && file_exists('../assets/uploads/' . $profile_image)) {
                            unlink('../assets/uploads/' . $profile_image);
                        }
                        $profile_image = $filename;
                    } else {
                        $error = "Failed to upload image.";
                    }
                }
            }

            if (empty($error)) {
                $update = $pdo->prepare("UPDATE users SET username = ?, profile_image = ? WHERE id = ?");
                $update->execute([$new_username, $profile_image, $user_id]);
                $_SESSION['username'] = $new_username;
                $_SESSION['profile_image'] = $profile_image;

                if (!empty($new_password)) {
                    if ($new_password !== $confirm_password) {
                        $error = "New password and confirmation do not match.";
                    } elseif (strlen($new_password) < 6) {
                        $error = "New password must be at least 6 characters.";
                    } else {
                        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_pass = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $update_pass->execute([$hashed, $user_id]);
                        $success = "Profile updated successfully! (Password changed)";
                    }
                } else {
                    $success = "Profile updated successfully!";
                }

                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
            }
        }
    }
}

include '../includes/header.php';
?>

<style>
    .profile-preview {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        margin-top: 10px;
    }
</style>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Your Profile</h3>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Profile Picture</label>
                <input type="file" name="profile_image" class="form-control" accept="image/*">
                <?php if ($user['profile_image']): ?>
                    <div class="mt-2">
                        <img src="/scholasys/assets/uploads/<?= $user['profile_image'] ?>" class="profile-preview" alt="Profile Preview">
                    </div>
                <?php else: ?>
                    <div class="mt-2 text-muted">No profile picture yet.</div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
                <small class="text-muted">Required to make any changes</small>
            </div>
            <div class="mb-3">
                <label>New Password (leave blank to keep current)</label>
                <input type="password" name="new_password" class="form-control">
            </div>
            <div class="mb-3">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>