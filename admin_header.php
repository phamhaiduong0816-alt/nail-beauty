<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Admin Header Layout
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Quản trị - ' . APP_NAME) ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset('images/logo.png') ?>">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Style CSS & Admin CSS -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <script>
        window.BASE_URL = '<?= rtrim(BASE_URL, '/') ?>';
    </script>
</head>
<body class="admin-body">

<!-- Sidebar -->
<?php require_once APP_PATH . '/views/layouts/admin_sidebar.php'; ?>

<!-- Main Content Area -->
<div class="admin-main">
    <!-- Topbar -->
    <header class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <h5 class="mb-0 fw-bold text-dark"><?= e($title ?? 'Bảng điều khiển') ?></h5>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted d-none d-md-inline" style="font-size: 0.88rem;">
                <i class="bi bi-clock me-1 text-danger"></i> Hôm nay: <strong><?= date('d/m/Y') ?></strong>
            </span>
            <a href="<?= url('booking') ?>" class="btn btn-sm btn-bloom">
                <i class="bi bi-plus-circle me-1"></i> Đặt lịch mới
            </a>
        </div>
    </header>

    <!-- Admin Body Content -->
    <div class="admin-content">
        <!-- Flash Alerts -->
        <?php foreach (['success', 'danger', 'warning', 'info'] as $type): ?>
            <?php if (Session::hasFlash($type)): ?>
                <div class="alert alert-<?= $type ?> alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
                    <i class="bi <?= $type === 'success' ? 'bi-check-circle-fill' : ($type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill') ?> fs-4 me-2"></i>
                    <div class="flex-grow-1">
                        <?= Session::getFlash($type) ?>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
