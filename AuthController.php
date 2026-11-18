<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Controller: AuthController (Đăng nhập, Đăng ký, Đăng xuất)
 */

require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Customer.php';
require_once APP_PATH . '/models/Staff.php';

class AuthController {
    private User $userModel;
    private Customer $customerModel;
    private Staff $staffModel;

    public function __construct() {
        $this->userModel = new User();
        $this->customerModel = new Customer();
        $this->staffModel = new Staff();
    }

    /**
     * Hiển thị trang đăng nhập
     */
    public function showLogin(): void {
        AuthMiddleware::preventAuthenticated();
        view('auth/login', [
            'title' => 'Đăng nhập - ' . APP_NAME
        ]);
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(): void {
        AuthMiddleware::preventAuthenticated();

        if (!Session::validateCsrf()) {
            Session::setFlash('danger', 'Lỗi xác thực bảo mật (CSRF). Vui lòng thử lại.');
            redirect('login');
        }

        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($login) || empty($password)) {
            Session::setFlash('danger', 'Vui lòng nhập tên đăng nhập/email và mật khẩu.');
            redirect('login');
        }

        $user = $this->userModel->findByUsernameOrEmail($login);

        if (!$user || !password_verify($password, $user['password'])) {
            Session::setFlash('danger', 'Tên đăng nhập hoặc mật khẩu không chính xác.');
            redirect('login');
        }

        if ($user['status'] !== 'active') {
            Session::setFlash('danger', 'Tài khoản của bạn đang bị tạm khóa. Vui lòng liên hệ quản trị viên.');
            redirect('login');
        }

        // Lấy thông tin hồ sơ theo vai trò
        $profile = null;
        if ($user['role'] === 'CUSTOMER') {
            $profile = $this->customerModel->findByUserId((int)$user['id']);
        } elseif ($user['role'] === 'STAFF') {
            $profile = $this->staffModel->findByUserId((int)$user['id']);
        }

        Session::login($user, $profile);
        Session::setFlash('success', 'Đăng nhập thành công! Chào mừng bạn quay trở lại.');

        // Phân quyền chuyển hướng
        if ($user['role'] === 'ADMIN' || $user['role'] === 'STAFF') {
            redirect('admin/dashboard');
        } else {
            // Khách hàng nếu đang đi từ luồng đặt lịch
            $returnUrl = $_SESSION['return_url'] ?? 'home';
            unset($_SESSION['return_url']);
            redirect($returnUrl);
        }
    }

    /**
     * Hiển thị trang đăng ký khách hàng mới
     */
    public function showRegister(): void {
        AuthMiddleware::preventAuthenticated();
        view('auth/register', [
            'title' => 'Đăng ký tài khoản - ' . APP_NAME
        ]);
    }

    /**
     * Xử lý đăng ký tài khoản khách hàng
     */
    public function register(): void {
        AuthMiddleware::preventAuthenticated();

        if (!Session::validateCsrf()) {
            Session::setFlash('danger', 'Lỗi bảo mật (CSRF). Vui lòng thử lại.');
            redirect('register');
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $rePass   = $_POST['password_confirmation'] ?? '';
        $address  = trim($_POST['address'] ?? '');

        // Validation phía backend
        if (empty($fullName) || empty($username) || empty($email) || empty($phone) || empty($password)) {
            Session::setFlash('danger', 'Vui lòng điền đầy đủ tất cả các trường bắt buộc.');
            redirect('register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('danger', 'Địa chỉ email không đúng định dạng.');
            redirect('register');
        }

        if (strlen($password) < 6) {
            Session::setFlash('danger', 'Mật khẩu phải chứa ít nhất 6 ký tự.');
            redirect('register');
        }

        if ($password !== $rePass) {
            Session::setFlash('danger', 'Mật khẩu xác nhận không khớp.');
            redirect('register');
        }

        if ($this->userModel->findByUsername($username)) {
            Session::setFlash('danger', 'Tên đăng nhập này đã được sử dụng. Vui lòng chọn tên khác.');
            redirect('register');
        }

        if ($this->userModel->findByEmail($email)) {
            Session::setFlash('danger', 'Địa chỉ email này đã được đăng ký tài khoản.');
            redirect('register');
        }

        // Tạo tài khoản trong Transaction
        Database::beginTransaction();
        try {
            $userId = $this->userModel->create([
                'username' => $username,
                'email'    => $email,
                'password' => $password,
                'role'     => 'CUSTOMER',
                'status'   => 'active',
            ]);

            $customerId = $this->customerModel->create([
                'user_id'   => $userId,
                'full_name' => $fullName,
                'phone'     => $phone,
                'address'   => $address,
            ]);

            Database::commit();

            // Tự động đăng nhập sau khi đăng ký
            $user = $this->userModel->findById($userId);
            $profile = $this->customerModel->findById($customerId);
            Session::login($user, $profile);

            Session::setFlash('success', 'Đăng ký tài khoản thành công! Bạn có thể đặt lịch ngay bây giờ.');
            redirect('booking');
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('danger', 'Đã xảy ra lỗi trong quá trình đăng ký. Vui lòng thử lại sau.');
            redirect('register');
        }
    }

    /**
     * Đăng xuất
     */
    public function logout(): void {
        Session::logout();
        Session::start();
        Session::setFlash('info', 'Bạn đã đăng xuất an toàn khỏi hệ thống.');
        redirect('login');
    }
}
