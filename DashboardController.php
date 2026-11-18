<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Controller: DashboardController (Thống kê quản trị & KPI)
 */

require_once APP_PATH . '/models/Customer.php';
require_once APP_PATH . '/models/Staff.php';
require_once APP_PATH . '/models/Service.php';
require_once APP_PATH . '/models/Appointment.php';
require_once APP_PATH . '/models/Invoice.php';

class DashboardController {
    private Customer $customerModel;
    private Staff $staffModel;
    private Service $serviceModel;
    private Appointment $appointmentModel;
    private Invoice $invoiceModel;

    public function __construct() {
        $this->customerModel = new Customer();
        $this->staffModel = new Staff();
        $this->serviceModel = new Service();
        $this->appointmentModel = new Appointment();
        $this->invoiceModel = new Invoice();
    }

    public function index(): void {
        AuthMiddleware::requireStaffOrAdmin();

        // 1. Thống kê KPI cốt lõi
        $totalCustomers     = $this->customerModel->countAll();
        $totalActiveStaff   = $this->staffModel->countActive();
        $totalActiveServices= $this->serviceModel->countActive();
        $appointmentsToday  = $this->appointmentModel->countToday();
        $pendingAppointments= $this->appointmentModel->countPending();
        $totalRevenue       = $this->invoiceModel->getTotalRevenue();
        $unpaidInvoices     = $this->invoiceModel->countUnpaid();

        // 2. Lịch hẹn gần đây
        $staffId = Session::isStaff() ? Session::staffId() : null;
        $recentFilters = $staffId ? ['staff_id' => $staffId] : [];
        $recentAppointments = array_slice($this->appointmentModel->getAll($recentFilters), 0, 8);

        // 3. Hóa đơn gần đây (chỉ admin xem)
        $recentInvoices = Session::isAdmin() ? array_slice($this->invoiceModel->getAll(), 0, 6) : [];

        view('dashboard/index', [
            'title'               => 'Bảng điều khiển Quản trị - ' . APP_NAME,
            'totalCustomers'      => $totalCustomers,
            'totalActiveStaff'    => $totalActiveStaff,
            'totalActiveServices' => $totalActiveServices,
            'appointmentsToday'   => $appointmentsToday,
            'pendingAppointments' => $pendingAppointments,
            'totalRevenue'        => $totalRevenue,
            'unpaidInvoices'      => $unpaidInvoices,
            'recentAppointments'  => $recentAppointments,
            'recentInvoices'      => $recentInvoices,
        ], 'admin');
    }
}
