<?php
require_once '../includes/config.php';
requireLogin();

if (!in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $expires = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

    if (empty($title) || empty($content)) {
        $error = "Title and content are required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$title, $content, $expires]);
        $success = "Announcement added successfully.";
    }
}

include '../includes/header.php';
?>

<h2>Add Announcement</h2>
<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<form method="post">
    <div class="mb-3">
        <label>Title</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Content</label>
        <textarea name="content" class="form-control" rows="6" required></textarea>
    </div>
    <div class="mb-3">
        <label>Expiration Date (optional)</label>
        <input type="date" name="expires_at" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Save Announcement</button>
    <a href="announcements.php" class="btn btn-secondary">Cancel</a>
</form>

<?php include '../includes/footer.php'; ?>