<?php
$page_title = 'Edit Graduate';
require_once '../includes/config.php';
requireLogin();

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM graduates WHERE id = ?");
$stmt->execute([$id]);
$grad = $stmt->fetch();
if (!$grad) {
    header('Location: graduates.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact_number']);
    $program = trim($_POST['program']);
    $year = (int)$_POST['graduation_year'];
    $status = $_POST['status'];
    $show_dir = isset($_POST['show_in_directory']) ? 1 : 0;

    $checkStmt = $pdo->prepare("SELECT id FROM graduates WHERE student_id = ? AND id != ?");
    $checkStmt->execute([$student_id, $id]);
    if ($checkStmt->fetch()) {
        $error = "Student ID '$student_id' already used by another graduate.";
    } else {
        $profile_image = $grad['profile_image'];
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $id . '_' . time() . '.' . $ext;
            $target = '../assets/uploads/' . $filename;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
                if ($profile_image && file_exists('../assets/uploads/' . $profile_image)) unlink('../assets/uploads/' . $profile_image);
                $profile_image = $filename;
            }
        }

        $cv_path = $grad['cv_path'];
        if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION);
            $filename = 'cv_' . $id . '_' . time() . '.' . $ext;
            $target = '../assets/uploads/' . $filename;
            if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $target)) {
                if ($cv_path && file_exists('../assets/uploads/' . $cv_path)) unlink('../assets/uploads/' . $cv_path);
                $cv_path = $filename;
            }
        }

        $update = $pdo->prepare("UPDATE graduates SET student_id=?, first_name=?, middle_name=?, last_name=?, email=?, contact_number=?, program=?, graduation_year=?, status=?, profile_image=?, cv_path=?, show_in_directory=? WHERE id=?");
        if ($update->execute([$student_id, $first_name, $middle_name, $last_name, $email, $contact, $program, $year, $status, $profile_image, $cv_path, $show_dir, $id])) {
            $success = "Graduate updated successfully!";
            $stmt = $pdo->prepare("SELECT * FROM graduates WHERE id = ?");
            $stmt->execute([$id]);
            $grad = $stmt->fetch();
        } else {
            $error = "Update failed.";
        }
    }
}

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Graduate</h3>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Student ID</label>
                    <input type="text" name="student_id" class="form-control" value="<?= htmlspecialchars($grad['student_id']) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>First Name</label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($grad['first_name']) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($grad['middle_name']) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($grad['last_name']) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($grad['email']) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($grad['contact_number']) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Program</label>
                    <input type="text" name="program" class="form-control" value="<?= htmlspecialchars($grad['program']) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Graduation Year</label>
                    <input type="number" name="graduation_year" class="form-control" value="<?= $grad['graduation_year'] ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= $grad['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $grad['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Profile Picture</label>
                    <input type="file" name="profile_image" class="form-control" accept="image/*">
                    <?php if ($grad['profile_image']): ?>
                        <img src="/scholasys/assets/uploads/<?= $grad['profile_image'] ?>" width="80" class="mt-2">
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label>CV (PDF)</label>
                    <input type="file" name="cv_file" class="form-control" accept=".pdf">
                    <?php if ($grad['cv_path']): ?>
                        <a href="/scholasys/assets/uploads/<?= $grad['cv_path'] ?>" target="_blank" class="btn btn-sm btn-info mt-2">View CV</a>
                    <?php endif; ?>
                </div>
                <div class="col-12 mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="show_in_directory" class="form-check-input" value="1" <?= $grad['show_in_directory'] ? 'checked' : '' ?>>
                        <label class="form-check-label">Show in public alumni directory</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Graduate</button>
            <a href="graduates.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>