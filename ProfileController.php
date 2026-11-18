<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Controller: ProfileController (Thông tin cá nhân & Đổi mật khẩu)
 */

require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Customer.php';
require_once APP_PATH . '/models/Staff.php';

class ProfileController {
    private User $userModel;
    private Customer $customerModel;
    private Staff $staffModel;

    public function __construct() {
        $this->userModel = new User();
        $this->customerModel = new Customer();
        $this->staffModel = new Staff();
    }

    public function index(): void {
        AuthMiddleware::requireLogin();

        $userId = Session::userId();
        $user = $this->userModel->findById($userId);
        $profile = null;

        if (Session::isCustomer()) {
            $profile = $this->customerModel->findByUserId($userId);
        } elseif (Session::isStaff()) {
            $profile = $this->staffModel->findByUserId($userId);
        }

        $layout = (Session::isAdmin() || Session::isStaff()) ? 'admin' : 'default';

        view('profile/index', [
            'title'   => 'Thông tin tài khoản - ' . APP_NAME,
            'user'    => $user,
            'profile' => $profile,
        ], $layout);
    }

    public function update(): void {
        AuthMiddleware::requireLogin();

        if (!Session::validateCsrf()) {
            Session::setFlash('danger', 'Lỗi xác thực (CSRF).');
            redirect('profile');
        }

        $userId   = Session::userId();
        $fullName = trim($_POST['full_name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $address  = trim($_POST['address'] ?? '');
        $bio      = trim($_POST['bio'] ?? '');
        $newPass  = $_POST['new_password'] ?? '';
        $rePass   = $_POST['confirm_password'] ?? '';

        if (empty($fullName) || empty($phone)) {
            Session::setFlash('danger', 'Họ tên và số điện thoại không được để trống.');
            redirect('profile');
        }

        // Cập nhật profile theo vai trò
        if (Session::isCustomer()) {
            $cust = $this->customerModel->findByUserId($userId);
            if ($cust) {
                $this->customerModel->update((int)$cust['id'], [
                    'full_name' => $fullName,
                    'phone'     => $phone,
                    'address'   => $address,
                    'notes'     => $cust['notes'] ?? null,
                ]);
                $_SESSION['customer']['full_name'] = $fullName;
                $_SESSION['customer']['phone'] = $phone;
                $_SESSION['customer']['address'] = $address;
            }
        } elseif (Session::isStaff()) {
            $staff = $this->staffModel->findByUserId($userId);
            if ($staff) {
                $this->staffModel->update((int)$staff['id'], [
                    'full_name' => $fullName,
                    'phone'     => $phone,
                    'title'     => $staff['title'],
                    'bio'       => $bio,
                    'status'    => $staff['status'],
                ]);
                $_SESSION['staff']['full_name'] = $fullName;
                $_SESSION['staff']['phone'] = $phone;
            }
        }

        // Đổi mật khẩu nếu có nhập
        if (!empty($newPass)) {
            if (strlen($newPass) < 6) {
                Session::setFlash('warning', 'Thông tin đã lưu, nhưng mật khẩu mới phải từ 6 ký tự trở lên.');
                redirect('profile');
            }
            if ($newPass !== $rePass) {
                Session::setFlash('warning', 'Thông tin đã lưu, nhưng mật khẩu xác nhận không trùng khớp.');
                redirect('profile');
            }
            $this->userModel->updatePassword($userId, $newPass);
        }

        Session::setFlash('success', 'Đã cập nhật thông tin cá nhân thành công!');
        redirect('profile');
    }
}
