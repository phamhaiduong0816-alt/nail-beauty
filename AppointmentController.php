<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Controller: AppointmentController (Quản lý trạng thái lịch hẹn, phân công, hủy lịch)
 */

require_once APP_PATH . '/models/Appointment.php';
require_once APP_PATH . '/models/Invoice.php';
require_once APP_PATH . '/models/Staff.php';
require_once APP_PATH . '/models/Customer.php';

class AppointmentController {
    private Appointment $appointmentModel;
    private Invoice $invoiceModel;
    private Staff $staffModel;
    private Customer $customerModel;

    public function __construct() {
        $this->appointmentModel = new Appointment();
        $this->invoiceModel = new Invoice();
        $this->staffModel = new Staff();
        $this->customerModel = new Customer();
    }

    /**
     * Danh sách lịch hẹn dành cho Admin và Staff
     */
    public function index(): void {
        AuthMiddleware::requireStaffOrAdmin();

        $status  = trim($_GET['status'] ?? '');
        $date    = trim($_GET['date'] ?? '');
        $staffId = (int)($_GET['staff_id'] ?? 0);
        $keyword = trim($_GET['keyword'] ?? '');

        // Nếu là Staff, chỉ xem lịch của chính mình
        if (Session::isStaff()) {
            $staffId = Session::staffId() ?? 0;
        }

        $filters = array_filter([
            'status'   => $status,
            'date'     => $date,
            'staff_id' => $staffId,
            'keyword'  => $keyword,
        ]);

        $appointments = $this->appointmentModel->getAll($filters);
        $staffList = $this->staffModel->getActiveStaff();

        view('appointments/index', [
            'title'        => 'Quản lý lịch hẹn - ' . APP_NAME,
            'appointments' => $appointments,
            'staffList'    => $staffList,
            'status'       => $status,
            'date'         => $date,
            'staffId'      => $staffId,
            'keyword'      => $keyword,
        ], 'admin');
    }

    /**
     * Chi tiết lịch hẹn
     */
    public function detail(): void {
        AuthMiddleware::requireStaffOrAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $apt = $this->appointmentModel->findById($id);

        if (!$apt) {
            Session::setFlash('danger', 'Không tìm thấy lịch hẹn.');
            redirect('admin/appointments');
        }

        // Nếu là Staff, kiểm tra xem có phải lịch của mình không
        if (Session::isStaff() && (int)$apt['staff_id'] !== Session::staffId()) {
            Session::setFlash('danger', 'Bạn không có quyền xem lịch hẹn của kỹ thuật viên khác.');
            redirect('admin/appointments');
        }

        $allStaff = $this->staffModel->getActiveStaff();

        view('appointments/detail', [
            'title'       => 'Chi tiết lịch hẹn #' . $apt['code'],
            'appointment' => $apt,
            'allStaff'    => $allStaff,
        ], 'admin');
    }

    /**
     * Cập nhật trạng thái lịch hẹn (Admin & Staff)
     * ĐẶC BIỆT: Khi chuyển sang 'completed' -> Tự động sinh đúng 1 hóa đơn
     */
    public function updateStatus(): void {
        AuthMiddleware::requireStaffOrAdmin();

        if (!Session::validateCsrf()) {
            Session::setFlash('danger', 'Lỗi bảo mật (CSRF).');
            redirect('admin/appointments');
        }

        $id = (int)($_POST['id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');

        $apt = $this->appointmentModel->findById($id);
        if (!$apt) {
            Session::setFlash('danger', 'Không tìm thấy lịch hẹn.');
            redirect('admin/appointments');
        }

        // Quyền hạn Staff: chỉ được cập nhật lịch của chính mình và chỉ được đổi in_progress / completed
        if (Session::isStaff()) {
            if ((int)$apt['staff_id'] !== Session::staffId()) {
                Session::setFlash('danger', 'Bạn không có quyền cập nhật lịch hẹn này.');
                redirect('admin/appointments');
            }
            if (!in_array($newStatus, ['in_progress', 'completed'], true)) {
                Session::setFlash('danger', 'Nhân viên chỉ có thể cập nhật trạng thái "Đang thực hiện" hoặc "Hoàn thành".');
                redirect('admin/appointments/detail?id=' . $id);
            }
        }

        // Nếu trạng thái mới là 'completed', tạo hóa đơn trong Transaction
        if ($newStatus === 'completed') {
            Database::beginTransaction();
            try {
                $this->appointmentModel->updateStatus($id, 'completed');
                // Tự động sinh hóa đơn (đảm bảo tính duy nhất 1-1)
                $invoiceId = $this->invoiceModel->createForAppointment($id);
                Database::commit();

                Session::setFlash('success', 'Dịch vụ đã hoàn thành! Hóa đơn thanh toán đã được tự động tạo thành công.');
                redirect('admin/invoices/detail?id=' . $invoiceId);
            } catch (Exception $e) {
                Database::rollBack();
                Session::setFlash('danger', 'Lỗi khi cập nhật hoàn thành dịch vụ và tạo hóa đơn: ' . $e->getMessage());
                redirect('admin/appointments/detail?id=' . $id);
            }
        } else {
            $this->appointmentModel->updateStatus($id, $newStatus);
            Session::setFlash('success', 'Đã cập nhật trạng thái lịch hẹn sang: ' . $newStatus);
            redirect('admin/appointments/detail?id=' . $id);
        }
    }

    /**
     * Đổi lịch / Phân công nhân viên mới (Admin Only)
     */
    public function assignStaff(): void {
        AuthMiddleware::requireAdmin();

        if (!Session::validateCsrf()) {
            Session::setFlash('danger', 'Lỗi xác thực (CSRF).');
            redirect('admin/appointments');
        }

        $id        = (int)($_POST['id'] ?? 0);
        $staffId   = (int)($_POST['staff_id'] ?? 0);
        $date      = trim($_POST['appointment_date'] ?? '');
        $startTime = trim($_POST['start_time'] ?? '');

        $apt = $this->appointmentModel->findById($id);
        if (!$apt) {
            Session::setFlash('danger', 'Không tìm thấy lịch hẹn.');
            redirect('admin/appointments');
        }

        if (strlen($startTime) === 5) {
            $startTime .= ':00';
        }

        // Tính giờ kết thúc theo thời lượng dịch vụ
        $duration = (int)$apt['duration_minutes'];
        $endTime = date('H:i:s', strtotime($startTime) + ($duration * 60));

        // Kiểm tra ca làm việc nhân viên
        if (!$this->appointmentModel->checkStaffWorkingHours($staffId, $date, $startTime, $endTime)) {
            Session::setFlash('danger', 'Kỹ thuật viên không có ca làm việc hoặc khung giờ nằm ngoài giờ làm việc.');
            redirect('admin/appointments/detail?id=' . $id);
        }

        // Kiểm tra xung đột thời gian (loại trừ lịch hiện tại)
        if ($this->appointmentModel->checkStaffConflict($staffId, $date, $startTime, $endTime, $id)) {
            Session::setFlash('danger', 'Khung giờ này kỹ thuật viên đã có lịch hẹn khác. Vui lòng chọn giờ hoặc kỹ thuật viên khác.');
            redirect('admin/appointments/detail?id=' . $id);
        }

        $this->appointmentModel->reschedule($id, $date, $startTime, $endTime, $staffId);
        Session::setFlash('success', 'Đã phân công lại kỹ thuật viên và cập nhật lịch hẹn thành công!');
        redirect('admin/appointments/detail?id=' . $id);
    }

    /**
     * Lịch hẹn của tôi (Dành cho khách hàng)
     */
    public function myAppointments(): void {
        AuthMiddleware::requireLogin();

        $customerId = Session::customerId();
        if (!$customerId) {
            $cust = $this->customerModel->findByUserId(Session::userId());
            $customerId = $cust['id'] ?? 0;
        }

        $appointments = $this->appointmentModel->getByCustomer((int)$customerId);

        view('appointments/my_appointments', [
            'title'        => 'Lịch hẹn của tôi - ' . APP_NAME,
            'appointments' => $appointments,
        ]);
    }

    /**
     * Khách hàng hủy lịch hẹn của chính mình
     */
    public function customerCancel(): void {
        AuthMiddleware::requireLogin();

        if (!Session::validateCsrf()) {
            Session::setFlash('danger', 'Lỗi xác thực (CSRF).');
            redirect('customer/appointments');
        }

        $id = (int)($_POST['id'] ?? 0);
        $apt = $this->appointmentModel->findById($id);

        if (!$apt) {
            Session::setFlash('danger', 'Lịch hẹn không tồn tại.');
            redirect('customer/appointments');
        }

        // Kiểm tra quyền sở hữu (IDOR check)
        $customerId = Session::customerId();
        if ((int)$apt['customer_id'] !== (int)$customerId) {
            Session::setFlash('danger', 'Bạn không có quyền thao tác trên lịch hẹn này.');
            redirect('customer/appointments');
        }

        // Chỉ cho hủy lịch đang ở trạng thái pending hoặc confirmed
        if (!in_array($apt['status'], ['pending', 'confirmed'], true)) {
            Session::setFlash('danger', 'Lịch hẹn đã bắt đầu hoặc đã kết thúc, không thể hủy.');
            redirect('customer/appointments');
        }

        $this->appointmentModel->updateStatus($id, 'cancelled');
        Session::setFlash('success', 'Bạn đã hủy lịch hẹn thành công.');
        redirect('customer/appointments');
    }
}
