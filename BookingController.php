<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Controller: BookingController (Quy trình đặt lịch & kiểm tra xung đột)
 */

require_once APP_PATH . '/models/Service.php';
require_once APP_PATH . '/models/Staff.php';
require_once APP_PATH . '/models/Appointment.php';
require_once APP_PATH . '/models/Customer.php';

class BookingController {
    private Service $serviceModel;
    private Staff $staffModel;
    private Appointment $appointmentModel;
    private Customer $customerModel;

    public function __construct() {
        $this->serviceModel = new Service();
        $this->staffModel = new Staff();
        $this->appointmentModel = new Appointment();
        $this->customerModel = new Customer();
    }

    /**
     * Giao diện đặt lịch
     */
    public function index(): void {
        // Nếu chưa đăng nhập, lưu URL dự kiến để sau khi login quay lại
        if (!Session::isLoggedIn()) {
            $_SESSION['return_url'] = 'booking' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
        }

        $selectedServiceId = (int)($_GET['service_id'] ?? 0);
        $services = $this->serviceModel->getActiveServices();
        $staffMembers = $this->staffModel->getActiveStaff();

        view('booking/index', [
            'title'             => 'Đặt lịch hẹn - ' . APP_NAME,
            'services'          => $services,
            'staffMembers'      => $staffMembers,
            'selectedServiceId' => $selectedServiceId,
            'customer'          => Session::customer(),
        ]);
    }

    /**
     * API trả về các khung giờ trống cho ngày và dịch vụ đã chọn (AJAX)
     */
    public function getAvailableSlots(): void {
        $serviceId = (int)($_GET['service_id'] ?? 0);
        $staffId   = (int)($_GET['staff_id'] ?? 0);
        $date      = trim($_GET['date'] ?? '');

        if (!$serviceId || !$date) {
            jsonResponse(['status' => 'error', 'message' => 'Thiếu dữ liệu dịch vụ hoặc ngày hẹn.'], 400);
        }

        $service = $this->serviceModel->findById($serviceId);
        if (!$service || !$service['is_active']) {
            jsonResponse(['status' => 'error', 'message' => 'Dịch vụ không tồn tại.'], 404);
        }

        $duration = (int)$service['duration_minutes'];

        // Các mốc giờ mở cửa: từ 08:30 đến 19:30 (bước nhảy 30 phút)
        $startLimit = strtotime('08:30:00');
        $endLimit   = strtotime('19:30:00');
        $slots = [];

        $current = $startLimit;
        while ($current <= $endLimit) {
            $slotStart = date('H:i:s', $current);
            $slotEnd   = date('H:i:s', $current + ($duration * 60));

            // Chỉ xét nếu giờ kết thúc không vượt quá 20:30
            if (strtotime($slotEnd) <= strtotime('20:30:00')) {
                $isAvailable = false;
                $assignedStaffName = null;

                if ($staffId > 0) {
                    // Kiểm tra nhân viên cụ thể
                    $hasHours = $this->appointmentModel->checkStaffWorkingHours($staffId, $date, $slotStart, $slotEnd);
                    $hasConflict = $this->appointmentModel->checkStaffConflict($staffId, $date, $slotStart, $slotEnd);
                    $isAvailable = ($hasHours && !$hasConflict);
                } else {
                    // Kiểm tra xem có bất kỳ nhân viên nào trống không
                    $availableStaffId = $this->appointmentModel->findAvailableStaff($date, $slotStart, $slotEnd);
                    $isAvailable = ($availableStaffId !== null);
                }

                $slots[] = [
                    'start_time'   => substr($slotStart, 0, 5),
                    'end_time'     => substr($slotEnd, 0, 5),
                    'is_available' => $isAvailable,
                ];
            }

            $current += 30 * 60; // Nhảy mỗi 30 phút
        }

        jsonResponse([
            'status'   => 'success',
            'date'     => $date,
            'duration' => $duration,
            'slots'    => $slots
        ]);
    }

    /**
     * Xử lý gửi yêu cầu đặt lịch
     */
    public function store(): void {
        AuthMiddleware::requireLogin();

        // Chỉ khách hàng hoặc admin mới được tạo lịch
        if (!Session::isCustomer() && !Session::isAdmin()) {
            Session::setFlash('danger', 'Chức năng đặt lịch chỉ dành cho tài khoản khách hàng.');
            redirect('home');
        }

        if (!Session::validateCsrf()) {
            Session::setFlash('danger', 'Lỗi xác thực (CSRF). Vui lòng thử lại.');
            redirect('booking');
        }

        $serviceId = (int)($_POST['service_id'] ?? 0);
        $staffId   = (int)($_POST['staff_id'] ?? 0); // 0 = Chọn nhân viên bất kỳ
        $date      = trim($_POST['appointment_date'] ?? '');
        $startTime = trim($_POST['start_time'] ?? '');
        $notes     = trim($_POST['notes'] ?? '');

        // 1. Kiểm tra dữ liệu đầu vào
        if (!$serviceId || empty($date) || empty($startTime)) {
            Session::setFlash('danger', 'Vui lòng chọn đầy đủ dịch vụ, ngày hẹn và giờ bắt đầu.');
            redirect('booking');
        }

        // Không cho đặt lịch trong quá khứ
        $today = date('Y-m-d');
        if ($date < $today) {
            Session::setFlash('danger', 'Không thể đặt lịch cho ngày đã qua trong quá khứ.');
            redirect('booking');
        }

        // 2. Xác định dịch vụ từ Database (Không tin giá trị client)
        $service = $this->serviceModel->findById($serviceId);
        if (!$service || !$service['is_active']) {
            Session::setFlash('danger', 'Dịch vụ đã chọn hiện không hoạt động hoặc không tồn tại.');
            redirect('booking');
        }

        // Chuẩn hóa giờ
        if (strlen($startTime) === 5) {
            $startTime .= ':00';
        }
        $durationMinutes = (int)$service['duration_minutes'];
        $endTime = date('H:i:s', strtotime($startTime) + ($durationMinutes * 60));

        // 3. Phân công & Kiểm tra nhân viên
        $assignedStaffId = null;

        if ($staffId > 0) {
            // Khách tự chọn nhân viên
            $staff = $this->staffModel->findById($staffId);
            if (!$staff || $staff['status'] !== 'active') {
                Session::setFlash('danger', 'Kỹ thuật viên đã chọn hiện không còn làm việc.');
                redirect('booking');
            }

            // Kiểm tra ca làm việc
            if (!$this->appointmentModel->checkStaffWorkingHours($staffId, $date, $startTime, $endTime)) {
                Session::setFlash('danger', 'Kỹ thuật viên ' . htmlspecialchars($staff['full_name']) . ' không có ca làm việc hoặc khung giờ hẹn nằm ngoài giờ làm việc trong ngày ' . formatDate($date) . '. Vui lòng chọn kỹ thuật viên hoặc giờ khác.');
                redirect('booking');
            }

            // Kiểm tra xung đột lịch hẹn
            if ($this->appointmentModel->checkStaffConflict($staffId, $date, $startTime, $endTime)) {
                Session::setFlash('danger', 'Rất tiếc! Kỹ thuật viên ' . htmlspecialchars($staff['full_name']) . ' đã có lịch hẹn trùng trong khung giờ ' . substr($startTime, 0, 5) . ' - ' . substr($endTime, 0, 5) . '. Vui lòng chọn khung giờ khác hoặc để tiệm tự động phân công.');
                redirect('booking');
            }

            $assignedStaffId = $staffId;
        } else {
            // Khách chọn nhân viên bất kỳ -> Tự động tìm nhân viên phù hợp và còn trống
            $assignedStaffId = $this->appointmentModel->findAvailableStaff($date, $startTime, $endTime);

            if (!$assignedStaffId) {
                Session::setFlash('danger', 'Hiện không có kỹ thuật viên nào có ca làm việc còn trống trong khung giờ ' . substr($startTime, 0, 5) . ' - ' . substr($endTime, 0, 5) . ' ngày ' . formatDate($date) . '. Vui lòng chọn mốc thời gian khác.');
                redirect('booking');
            }
        }

        // 4. Lấy Customer ID
        $customerId = null;
        if (Session::isCustomer()) {
            $customerId = Session::customerId();
            if (!$customerId) {
                $cust = $this->customerModel->findByUserId(Session::userId());
                $customerId = $cust['id'] ?? null;
            }
        } else {
            // Admin đặt hộ khách
            $customerId = (int)($_POST['customer_id'] ?? 1);
        }

        if (!$customerId) {
            Session::setFlash('danger', 'Không tìm thấy hồ sơ khách hàng.');
            redirect('booking');
        }

        // 5. Lưu lịch hẹn trong Transaction
        Database::beginTransaction();
        try {
            // Khóa chốt giá dịch vụ lúc đặt
            $priceAtBooking = $service['price'];

            $aptId = $this->appointmentModel->create([
                'customer_id'      => $customerId,
                'service_id'       => $serviceId,
                'staff_id'         => $assignedStaffId,
                'appointment_date' => $date,
                'start_time'       => $startTime,
                'end_time'         => $endTime,
                'price_at_booking' => $priceAtBooking,
                'status'           => 'pending',
                'notes'            => $notes,
            ]);

            Database::commit();

            $appointment = $this->appointmentModel->findById($aptId);
            Session::setFlash('success', 'Đặt lịch thành công! Mã lịch hẹn của bạn là: ' . $appointment['code']);
            redirect('booking/success?code=' . urlencode($appointment['code']));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('danger', 'Đã xảy ra lỗi khi lưu lịch hẹn. Vui lòng thử lại.');
            redirect('booking');
        }
    }

    /**
     * Trang thông báo đặt lịch thành công
     */
    public function success(): void {
        $code = trim($_GET['code'] ?? '');
        $appointment = $code ? $this->appointmentModel->findByCode($code) : null;

        if (!$appointment) {
            redirect('booking');
        }

        view('booking/success', [
            'title'       => 'Xác nhận đặt lịch hẹn - ' . APP_NAME,
            'appointment' => $appointment,
        ]);
    }
}
