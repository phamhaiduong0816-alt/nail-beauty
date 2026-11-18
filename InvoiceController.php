<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Controller: InvoiceController (Quản lý hóa đơn & thanh toán)
 */

require_once APP_PATH . '/models/Invoice.php';
require_once APP_PATH . '/models/Payment.php';
require_once APP_PATH . '/models/Customer.php';

class InvoiceController {
    private Invoice $invoiceModel;
    private Payment $paymentModel;
    private Customer $customerModel;

    public function __construct() {
        $this->invoiceModel = new Invoice();
        $this->paymentModel = new Payment();
        $this->customerModel = new Customer();
    }

    /**
     * Danh sách hóa đơn dành cho Admin
     */
    public function index(): void {
        AuthMiddleware::requireAdmin();

        $status  = trim($_GET['status'] ?? '');
        $keyword = trim($_GET['keyword'] ?? '');

        $filters = array_filter([
            'status'  => $status,
            'keyword' => $keyword,
        ]);

        $invoices = $this->invoiceModel->getAll($filters);

        view('invoices/index', [
            'title'    => 'Quản lý Hóa đơn - ' . APP_NAME,
            'invoices' => $invoices,
            'status'   => $status,
            'keyword'  => $keyword,
        ], 'admin');
    }

    /**
     * Chi tiết hóa đơn
     */
    public function detail(): void {
        AuthMiddleware::requireLogin();

        $id = (int)($_GET['id'] ?? 0);
        $invoice = $this->invoiceModel->findById($id);

        if (!$invoice) {
            Session::setFlash('danger', 'Hóa đơn không tồn tại.');
            redirect(Session::isAdmin() ? 'admin/invoices' : 'customer/invoices');
        }

        // Chống IDOR: Khách hàng chỉ được xem hóa đơn của chính mình
        if (Session::isCustomer() && (int)$invoice['customer_id'] !== Session::customerId()) {
            Session::setFlash('danger', 'Bạn không có quyền truy cập hóa đơn này.');
            redirect('customer/invoices');
        }

        $payments = $this->paymentModel->getByInvoiceId($id);

        $layout = (Session::isAdmin() || Session::isStaff()) ? 'admin' : 'default';

        view('invoices/detail', [
            'title'    => 'Hóa đơn #' . $invoice['invoice_code'],
            'invoice'  => $invoice,
            'payments' => $payments,
        ], $layout);
    }

    /**
     * Ghi nhận thanh toán hóa đơn (Admin Only)
     */
    public function markPaid(): void {
        AuthMiddleware::requireAdmin();

        if (!Session::validateCsrf()) {
            Session::setFlash('danger', 'Lỗi xác thực (CSRF).');
            redirect('admin/invoices');
        }

        $id        = (int)($_POST['id'] ?? 0);
        $method    = trim($_POST['payment_method'] ?? 'cash');
        $reference = trim($_POST['transaction_reference'] ?? '');
        $notes     = trim($_POST['notes'] ?? '');

        $invoice = $this->invoiceModel->findById($id);
        if (!$invoice) {
            Session::setFlash('danger', 'Hóa đơn không tồn tại.');
            redirect('admin/invoices');
        }

        if ($invoice['status'] === 'paid') {
            Session::setFlash('warning', 'Hóa đơn này đã được thanh toán trước đó.');
            redirect('admin/invoices/detail?id=' . $id);
        }

        $success = $this->invoiceModel->markAsPaid($id, $method, $reference ?: null, $notes ?: null);

        if ($success) {
            Session::setFlash('success', 'Đã ghi nhận thanh toán thành công cho hóa đơn #' . $invoice['invoice_code']);
        } else {
            Session::setFlash('danger', 'Không thể ghi nhận thanh toán. Vui lòng thử lại.');
        }

        redirect('admin/invoices/detail?id=' . $id);
    }

    /**
     * Hóa đơn của tôi (Dành cho khách hàng)
     */
    public function myInvoices(): void {
        AuthMiddleware::requireLogin();

        $customerId = Session::customerId();
        if (!$customerId) {
            $cust = $this->customerModel->findByUserId(Session::userId());
            $customerId = $cust['id'] ?? 0;
        }

        $invoices = $this->invoiceModel->getByCustomer((int)$customerId);

        view('invoices/my_invoices', [
            'title'    => 'Hóa đơn dịch vụ của tôi - ' . APP_NAME,
            'invoices' => $invoices,
        ]);
    }
}
