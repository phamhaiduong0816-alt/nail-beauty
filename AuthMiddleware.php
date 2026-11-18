<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Authentication & Authorization Middleware
 */

class AuthMiddleware {
    /**
     * Bắt buộc phải đăng nhập
     */
    public static function requireLogin(): void {
        if (!Session::isLoggedIn()) {
            Session::setFlash('warning', 'Vui lòng đăng nhập để tiếp tục.');
            redirect('login');
        }
    }

    /**
     * Bắt buộc quyền Quản trị viên (ADMIN)
     */
    public static function requireAdmin(): void {
        self::requireLogin();
        if (!Session::isAdmin()) {
            Session::setFlash('danger', 'Bạn không có quyền truy cập chức năng này.');
            redirect('home');
        }
    }

    /**
     * Bắt buộc quyền Nhân viên hoặc Quản trị viên (STAFF hoặc ADMIN)
     */
    public static function requireStaffOrAdmin(): void {
        self::requireLogin();
        if (!Session::isAdmin() && !Session::isStaff()) {
            Session::setFlash('danger', 'Khu vực chỉ dành cho nhân viên và quản trị viên.');
            redirect('home');
        }
    }

    /**
     * Bắt buộc quyền Khách hàng (CUSTOMER)
     */
    public static function requireCustomer(): void {
        self::requireLogin();
        if (!Session::isCustomer()) {
            Session::setFlash('warning', 'Chức năng này dành cho tài khoản khách hàng.');
            redirect('admin/dashboard');
        }
    }

    /**
     * Nếu đã đăng nhập thì chuyển hướng ra ngoài (dành cho trang Login / Register)
     */
    public static function preventAuthenticated(): void {
        if (Session::isLoggedIn()) {
            if (Session::isAdmin() || Session::isStaff()) {
                redirect('admin/dashboard');
            } else {
                redirect('home');
            }
        }
    }
}
