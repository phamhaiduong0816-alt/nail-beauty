<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Admin Sidebar Layout
 */
$currentUri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="<?= url('admin/dashboard') ?>" class="sidebar-brand d-flex align-items-center gap-2">
            <img src="<?= asset('images/logo.png') ?>" alt="<?= APP_NAME ?>" height="36" class="rounded bg-white p-1" style="object-fit: contain;">
            <div>
                <span class="fw-bold">NAIL</span> &amp; BEAUTY
                <div class="text-white-50 fs-8 text-uppercase" style="letter-spacing: 1px; font-size: 0.65rem;">Hệ Thống Quản Trị</div>
            </div>
        </a>
    </div>

    <ul class="sidebar-menu">
        <li class="menu-header">Chính</li>
        <li class="menu-item">
            <a href="<?= url('admin/dashboard') ?>" class="menu-link <?= str_contains($currentUri, 'dashboard') ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2"></i>
                <span>Bảng điều khiển</span>
            </a>
        </li>

        <li class="menu-header">Nghiệp vụ Salon</li>
        <li class="menu-item">
            <a href="<?= url('admin/appointments') ?>" class="menu-link <?= str_contains($currentUri, 'appointments') ? 'active' : '' ?>">
                <i class="bi bi-calendar-check"></i>
                <span>Quản lý Lịch hẹn</span>
            </a>
        </li>

        <?php if (Session::isAdmin()): ?>
            <li class="menu-item">
                <a href="<?= url('admin/invoices') ?>" class="menu-link <?= str_contains($currentUri, 'invoices') ? 'active' : '' ?>">
                    <i class="bi bi-receipt"></i>
                    <span>Hóa đơn & Doanh thu</span>
                </a>
            </li>

            <li class="menu-header">Danh mục & Nhân sự</li>
            <li class="menu-item">
                <a href="<?= url('admin/services') ?>" class="menu-link <?= str_contains($currentUri, 'services') ? 'active' : '' ?>">
                    <i class="bi bi-palette"></i>
                    <span>Dịch vụ Nail & Spa</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= url('admin/staff') ?>" class="menu-link <?= str_contains($currentUri, 'staff') ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>Kỹ thuật viên & Ca làm</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= url('admin/customers') ?>" class="menu-link <?= str_contains($currentUri, 'customers') ? 'active' : '' ?>">
                    <i class="bi bi-person-heart"></i>
                    <span>Hồ sơ Khách hàng</span>
                </a>
            </li>
        <?php endif; ?>

        <li class="menu-header">Hệ thống</li>
        <li class="menu-item">
            <a href="<?= url('profile') ?>" class="menu-link <?= str_contains($currentUri, 'profile') ? 'active' : '' ?>">
                <i class="bi bi-person-gear"></i>
                <span>Tài khoản của tôi</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="<?= url('home') ?>" target="_blank" class="menu-link text-white-50">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>Xem Website tiệm</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-snippet">
            <div class="user-avatar-sm">
                <?= strtoupper(substr(Session::user()['username'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="flex-grow-1 overflow-hidden">
                <div class="text-white text-truncate fw-semibold" style="font-size: 0.9rem;">
                    <?= e(Session::user()['username'] ?? 'User') ?>
                </div>
                <div class="badge bg-danger rounded-pill" style="font-size: 0.65rem;">
                    <?= e(Session::role() ?? 'STAFF') ?>
                </div>
            </div>
            <a href="<?= url('logout') ?>" class="text-white-50 fs-5" title="Đăng xuất">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</aside>
