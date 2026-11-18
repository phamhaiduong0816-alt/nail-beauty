<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Client Header Layout
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? APP_NAME) ?></title>
    <meta name="description" content="<?= e(APP_SLOGAN) ?> - Dịch vụ chăm sóc móng, mi, spa thư giãn chuẩn phong cách Hàn - Nhật tại TP.HCM.">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset('images/logo.png') ?>">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <script>
        window.BASE_URL = '<?= rtrim(BASE_URL, '/') ?>';
    </script>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-bloom sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('home') ?>">
            <img src="<?= asset('images/logo.png') ?>" alt="<?= APP_NAME ?>" class="brand-logo">
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#bloomNavbar">
            <i class="bi bi-list fs-2 text-dark"></i>
        </button>
        <div class="collapse navbar-collapse" id="bloomNavbar">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= empty($_GET['url']) || $_GET['url'] === 'home' ? 'active' : '' ?>" href="<?= url('home') ?>">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('services') ?>">Dịch vụ Nail & Spa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('booking') ?>">Đặt lịch ngay</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if (Session::isLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-bloom dropdown-toggle d-flex align-items-center gap-2 py-2" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5"></i>
                            <span><?= e(Session::user()['username']) ?></span>
                            <span class="badge bg-danger rounded-pill fs-7"><?= e(Session::role()) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                            <?php if (Session::isAdmin() || Session::isStaff()): ?>
                                <li>
                                    <a class="dropdown-item py-2 text-danger fw-semibold" href="<?= url('admin/dashboard') ?>">
                                        <i class="bi bi-speedometer2 me-2"></i>Trang quản trị (Dashboard)
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            
                            <?php if (Session::isCustomer()): ?>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= url('customer/appointments') ?>">
                                        <i class="bi bi-calendar-check me-2 text-primary"></i>Lịch hẹn của tôi
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= url('customer/invoices') ?>">
                                        <i class="bi bi-receipt me-2 text-primary"></i>Hóa đơn dịch vụ
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li>
                                <a class="dropdown-item py-2" href="<?= url('profile') ?>">
                                    <i class="bi bi-person-gear me-2"></i>Thông tin tài khoản
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="<?= url('logout') ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= url('login') ?>" class="btn btn-outline-bloom py-2">Đăng nhập</a>
                    <a href="<?= url('register') ?>" class="btn btn-bloom py-2">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Flash Alerts Notification Container -->
<div class="container mt-3">
    <?php foreach (['success', 'danger', 'warning', 'info'] as $type): ?>
        <?php if (Session::hasFlash($type)): ?>
            <div class="alert alert-<?= $type ?> alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bi <?= $type === 'success' ? 'bi-check-circle-fill' : ($type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill') ?> fs-4 me-2"></i>
                <div class="flex-grow-1">
                    <?= Session::getFlash($type) ?>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
