<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Controller: StaffController (Quản lý nhân viên & Lịch làm việc)
 */

require_once APP_PATH . '/models/Staff.php';
require_once APP_PATH . '/models/User.php';

class StaffController {
    private Staff $staffModel;
    private User $userModel;

    public function __construct() {
        $this->staffModel = new Staff();
        $this->userModel = new User();
    }

    /**
     * Danh sách nhân viên
     */
    public function index(): void {
        AuthMiddleware::requireAdmin();

        $status = trim($_GET['status'] ?? '');
        $staffMembers = $this->staffModel->getAll($status ?: null);

        view('staff/index', [
            'title'        => 'Quản lý Nhân viên - ' . APP_NAME,
            'staffMembers' => $staffMembers,
            'status'       => $status,
        ], 'admin');
    }

    /**
     * Form thêm nhân viên mới
     */
    public function create(): void {
        AuthMiddleware::requireAdmin();
        view('staff/form', [
            'title' => 'Thêm Kỹ thuật viên mới',
            'staff' => null,
            'isEdit'=> false,
        ], 'admin');
    }

    /**
     * Lưu nhân viên mới
     */
    public function store(): void {
        AuthMiddleware::requireAdmin();

        if (!Session::validateCsrf()) {
            Session::setFlash('danger', 'Lỗi bảo mật (CSRF).');
            redirect('admin/staff');
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? 'staff123';
        $title    = trim($_POST['title'] ?? 'Kỹ thuật viên Nail & Spa');
        $bio      = trim($_POST['bio'] ?? '');
        $status   = trim($_POST['status'] ?? 'active');

        if (empty($fullName) || empty($username) || empty($email) || empty($phone)) {
            Session::setFlash('danger', 'Vui lòng điền đầy đủ các thông tin bắt buộc.');
            redirect('admin/staff/create');
        }

        if ($this->userModel->findByUsername($username)) {
            Session::setFlash('danger', 'Tên đăng nhập đã tồn tại.');
            redirect('admin/staff/create');
        }

        if ($this->userModel->findByEmail($email)) {
            Session::setFlash('danger', 'Email đã được sử dụng.');
            redirect('admin/staff/create');
        }

        Database::beginTransaction();
        try {
            $userId = $this->userModel->create([
                'username' => $username,
                'email'    => $email,
                'password' => $password,
                'role'     => 'STAFF',
                'status'   => $status,
            ]);

            $this->staffModel->create([
                'user_id'   => $userId,
                'full_name' => $fullName,
                'phone'     => $phone,
                'title'     => $title,
                'bio'       => $bio,
                'status'    => $status,
            ]);

            Database::commit();
            Session::setFlash('success', 'Đã thêm kỹ thuật viên mới thành công (Kèm lịch làm việc mặc định 7 ngày).');
            redirect('admin/staff');
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('danger', 'Lỗi thêm nhân viên: ' . $e->getMessage());
            redirect('admin/staff/create');
        }
    }

    /**
     * Form sửa thông tin nhân viên
     */
    public function edit(): void {
        AuthMiddleware::requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $staff = $this->staffModel->findById($id);

        if (!$staff) {
            Session::setFlash('danger', 'Nhân viên không tồn tại.');
            redirect('admin/staff');
        }

        view('staff/form', [
            'title' => 'Chỉnh sửa nhân viên: ' . $staff['full_name'],
            'staff' => $staff,
            'isEdit'=> true,
        ], 'admin');
    }

    /**
     * Cập nhật thông tin nhân viên
     */
    public function update(): void {
        AuthMiddleware::requireAdmin();

        if (!Session::validateCsrf()) {
            Session::setFlash('danger', 'Lỗi xác thực (CSRF).');
            redirect('admin/staff');
        }

        $id       = (int)($_POST['id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $title    = trim($_POST['title'] ?? 'Kỹ thuật viên');
        $bio      = trim($_POST['bio'] ?? '');
        $status   = trim($_POST['status'] ?? 'active');

        $staff = $this->staffModel->findById($id);
        if (!$staff) {
            Session::setFlash('danger', 'Nhân viên không tồn tại.');
            redirect('admin/staff');
        }

        $this->staffModel->update($id, [
            'full_name' => $fullName,
            'phone'     => $phone,
            'title'     => $title,
            'bio'       => $bio,
            'status'    => $status,
        ]);

        // Cập nhật trạng thái user tài khoản liên kết
        $this->userModel->updateStatus((int)$staff['user_id'], $status);

        Session::setFlash('success', 'Đã cập nhật thông tin nhân viên thành công.');
        redirect('admin/staff');
    }

    /**
     * Giao diện cấu hình ca làm việc trong tuần
     */
    public function workingHours(): void {
        AuthMiddleware::requireAdmin();

        $staffId = (int)($_GET['id'] ?? 0);
        $staff = $this->staffModel->findById($staffId);

        if (!$staff) {
            Session::setFlash('danger', 'Không tìm thấy nhân viên.');
            redirect('admin/staff');
        }

        $hours = $this->staffModel->getWorkingHours($staffId);

        // Map theo thứ (1: Thứ Hai -> 7: Chủ Nhật)
        $hoursMap = [];
        foreach ($hours as $h) {
            $hoursMap[$h['day_of_week']] = $h;
        }

        view('staff/working_hours', [
            'title'    => 'Lịch làm việc: ' . $staff['full_name'],
            'staff'    => $staff,
            'hoursMap' => $hoursMap,
        ], 'admin');
    }

    /**
     * Lưu ca làm việc trong tuần
     */
    public function saveWorkingHours(): void {
        AuthMiddleware::requireAdmin();

        if (!Session::validateCsrf()) {
            Session::setFlash('danger', 'Lỗi xác thực (CSRF).');
            redirect('admin/staff');
        }

        $staffId = (int)($_POST['staff_id'] ?? 0);
        $staff = $this->staffModel->findById($staffId);

        if (!$staff) {
            Session::setFlash('danger', 'Không tìm thấy nhân viên.');
            redirect('admin/staff');
        }

        $daysData = $_POST['days'] ?? [];

        $formatted = [];
        for ($d = 1; $d <= 7; $d++) {
            $formatted[$d] = [
                'is_working' => isset($daysData[$d]['is_working']) ? 1 : 0,
                'start_time' => !empty($daysData[$d]['start_time']) ? $daysData[$d]['start_time'] . ':00' : '08:30:00',
                'end_time'   => !empty($daysData[$d]['end_time']) ? $daysData[$d]['end_time'] . ':00' : '20:00:00',
            ];
        }

        $this->staffModel->saveWorkingHours($staffId, $formatted);

        Session::setFlash('success', 'Đã lưu cấu hình ca làm việc cho ' . htmlspecialchars($staff['full_name']) . ' thành công!');
        redirect('admin/staff/hours?id=' . $staffId);
    }
}
