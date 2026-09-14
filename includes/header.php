<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ScholaSys | Graduate Management</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="/scholasys/assets/css/style.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                    <?php if (!empty($_SESSION['profile_image'])): ?>
                        <img src="/scholasys/assets/uploads/<?= $_SESSION['profile_image'] ?>" class="rounded-circle" width="25" height="25" style="object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($_SESSION['username'] ?? '') ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/scholasys/pages/my_profile.php"><i class="fas fa-user-edit"></i> My Profile</a></li>
                    <li><a class="dropdown-item" href="/scholasys/pages/change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
                    <li><a class="dropdown-item" href="/scholasys/pages/twofa_setup.php"><i class="fas fa-shield-alt"></i> Two‑Factor Auth</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/scholasys/public/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="/scholasys/pages/dashboard.php" class="brand-link">
            <i class="fas fa-graduation-cap brand-image" style="font-size: 24px;"></i>
            <span class="brand-text font-weight-light">ScholaSys</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
                <div class="image">
                    <?php if (!empty($_SESSION['profile_image'])): ?>
                        <img src="/scholasys/assets/uploads/<?= $_SESSION['profile_image'] ?>" class="img-circle elevation-2" width="35" height="35" style="object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user-circle fa-2x"></i>
                    <?php endif; ?>
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></a>
                    <small><?= ucfirst($_SESSION['role'] ?? '') ?></small>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item"><a href="/scholasys/pages/dashboard.php" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
                    <li class="nav-item"><a href="/scholasys/pages/graduates.php" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Graduates</p></a></li>
                    <li class="nav-item"><a href="/scholasys/pages/reports.php" class="nav-link"><i class="nav-icon fas fa-chart-bar"></i><p>Reports</p></a></li>
                    <li class="nav-item"><a href="/scholasys/pages/analytics.php" class="nav-link"><i class="nav-icon fas fa-chart-pie"></i><p>Analytics</p></a></li>
                    <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'staff'])): ?>
                    <li class="nav-item"><a href="/scholasys/pages/events.php" class="nav-link"><i class="nav-icon fas fa-calendar-alt"></i><p>Events</p></a></li>
                    <li class="nav-item"><a href="/scholasys/pages/announcements.php" class="nav-link"><i class="nav-icon fas fa-newspaper"></i><p>News</p></a></li>
                    <?php endif; ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item"><a href="/scholasys/pages/users.php" class="nav-link"><i class="nav-icon fas fa-users-cog"></i><p>User Management</p></a></li>
                    <li class="nav-item"><a href="/scholasys/pages/import_graduates.php" class="nav-link"><i class="nav-icon fas fa-file-import"></i><p>Import CSV</p></a></li>
                    <li class="nav-item"><a href="/scholasys/pages/custom_report.php" class="nav-link"><i class="nav-icon fas fa-chart-line"></i><p>Custom Report</p></a></li>
                    <li class="nav-item"><a href="/scholasys/pages/newsletter.php" class="nav-link"><i class="nav-icon fas fa-envelope"></i><p>Newsletter</p></a></li>
                    <?php endif; ?>
                    <li class="nav-header">PUBLIC</li>
                    <li class="nav-item"><a href="/scholasys/public/directory.php" class="nav-link"><i class="nav-icon fas fa-address-book"></i><p>Alumni Directory</p></a></li>
                    <li class="nav-item"><a href="/scholasys/public/survey.php" class="nav-link"><i class="nav-icon fas fa-poll"></i><p>Tracer Survey</p></a></li>
                    <li class="nav-item"><a href="/scholasys/public/events_list.php" class="nav-link"><i class="nav-icon fas fa-calendar-check"></i><p>Event Registration</p></a></li>
                    <li class="nav-item"><a href="/scholasys/pages/my_profile.php" class="nav-link"><i class="nav-icon fas fa-user-edit"></i><p>My Profile</p></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1><?= $page_title ?? 'ScholaSys' ?></h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">