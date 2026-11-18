<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Controller: CustomerController (Quản lý khách hàng)
 */

require_once APP_PATH . '/models/Customer.php';
require_once APP_PATH . '/models/Appointment.php';

class CustomerController {
    private Customer $customerModel;
    private Appointment $appointmentModel;

    public function __construct() {
        $this->customerModel = new Customer();
        $this->appointmentModel = new Appointment();
    }

    /**
     * Danh sách khách hàng
     */
    public function index(): void {
        AuthMiddleware::requireAdmin();

        $keyword = trim($_GET['keyword'] ?? '');
        $customers = $this->customerModel->getAll($keyword ?: null);

        view('customers/index', [
            'title'     => 'Quản lý Khách hàng - ' . APP_NAME,
            'customers' => $customers,
            'keyword'   => $keyword,
        ], 'admin');
    }

    /**
     * Chi tiết khách hàng và lịch sử làm đẹp
     */
    public function detail(): void {
        AuthMiddleware::requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $customer = $this->customerModel->findById($id);

        if (!$customer) {
            Session::setFlash('danger', 'Khách hàng không tồn tại.');
            redirect('admin/customers');
        }

        $appointments = $this->appointmentModel->getByCustomer($id);

        view('customers/detail', [
            'title'        => 'Khách hàng: ' . $customer['full_name'],
            'customer'     => $customer,
            'appointments' => $appointments,
        ], 'admin');
    }
}
