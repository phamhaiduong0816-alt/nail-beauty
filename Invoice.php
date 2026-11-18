<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Model: Invoice (Quản lý hóa đơn & thanh toán dịch vụ)
 */

require_once APP_PATH . '/helpers/Database.php';

class Invoice {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array {
        $sql = "SELECT inv.*,
                       a.code AS appointment_code, a.appointment_date, a.start_time, a.end_time, a.price_at_booking, a.notes AS appointment_notes,
                       c.full_name AS customer_name, c.phone AS customer_phone, c.address AS customer_address,
                       u_c.email AS customer_email,
                       s.full_name AS staff_name, s.phone AS staff_phone, s.title AS staff_title,
                       srv.name AS service_name, srv.code AS service_code, srv.duration_minutes
                FROM invoices inv
                JOIN appointments a ON inv.appointment_id = a.id
                JOIN customers c ON inv.customer_id = c.id
                JOIN users u_c ON c.user_id = u_c.id
                JOIN staff s ON inv.staff_id = s.id
                JOIN services srv ON a.service_id = srv.id
                WHERE inv.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByAppointmentId(int $appointmentId): ?array {
        $stmt = $this->db->prepare("SELECT id FROM invoices WHERE appointment_id = :aid LIMIT 1");
        $stmt->execute(['aid' => $appointmentId]);
        $id = $stmt->fetchColumn();
        return $id ? $this->findById((int)$id) : null;
    }

    public function getAll(array $filters = []): array {
        $sql = "SELECT inv.*,
                       a.code AS appointment_code, a.appointment_date,
                       c.full_name AS customer_name, c.phone AS customer_phone,
                       s.full_name AS staff_name,
                       srv.name AS service_name
                FROM invoices inv
                JOIN appointments a ON inv.appointment_id = a.id
                JOIN customers c ON inv.customer_id = c.id
                JOIN staff s ON inv.staff_id = s.id
                JOIN services srv ON a.service_id = srv.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND inv.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['customer_id'])) {
            $sql .= " AND inv.customer_id = :customer_id";
            $params['customer_id'] = $filters['customer_id'];
        }

        if (!empty($filters['keyword'])) {
            $sql .= " AND (inv.invoice_code LIKE :kw OR a.code LIKE :kw OR c.full_name LIKE :kw OR c.phone LIKE :kw)";
            $params['kw'] = "%" . $filters['keyword'] . "%";
        }

        $sql .= " ORDER BY inv.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getByCustomer(int $customerId): array {
        return $this->getAll(['customer_id' => $customerId]);
    }

    /**
     * TẠO HÓA ĐƠN DUY NHẤT CHO LỊCH HẸN KHI HOÀN THÀNH (COMPLETED)
     * Đảm bảo không sinh 2 hóa đơn cho 1 lịch hẹn
     */
    public function createForAppointment(int $appointmentId): ?int {
        // Kiểm tra xem đã có hóa đơn cho lịch này chưa
        $existing = $this->findByAppointmentId($appointmentId);
        if ($existing) {
            return (int)$existing['id'];
        }

        // Lấy thông tin lịch hẹn để chốt tiền
        $stmt = $this->db->prepare("SELECT customer_id, staff_id, price_at_booking, appointment_date FROM appointments WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $appointmentId]);
        $apt = $stmt->fetch();

        if (!$apt) {
            return null;
        }

        $datePart = date('Ymd');
        $randPart = strtoupper(substr(uniqid(), -4));
        $invoiceCode = "INV-{$datePart}-{$randPart}";

        $sql = "INSERT INTO invoices (invoice_code, appointment_id, customer_id, staff_id, total_amount, status)
                VALUES (:invoice_code, :appointment_id, :customer_id, :staff_id, :total_amount, 'unpaid')";
        $insertStmt = $this->db->prepare($sql);
        $insertStmt->execute([
            'invoice_code'   => $invoiceCode,
            'appointment_id' => $appointmentId,
            'customer_id'    => $apt['customer_id'],
            'staff_id'       => $apt['staff_id'],
            'total_amount'   => $apt['price_at_booking'],
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * GHI NHẬN THANH TOÁN HÓA ĐƠN (Transaction đảm bảo an toàn)
     */
    public function markAsPaid(int $invoiceId, string $method = 'cash', ?string $reference = null, ?string $notes = null): bool {
        $inv = $this->findById($invoiceId);
        if (!$inv || $inv['status'] === 'paid') {
            return false;
        }

        Database::beginTransaction();
        try {
            // Cập nhật trạng thái hóa đơn
            $stmt = $this->db->prepare("UPDATE invoices SET status = 'paid', paid_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $invoiceId]);

            // Thêm bản ghi vào payments
            $payStmt = $this->db->prepare("
                INSERT INTO payments (invoice_id, amount, payment_method, transaction_reference, notes, status)
                VALUES (:invoice_id, :amount, :payment_method, :ref, :notes, 'success')
            ");
            $payStmt->execute([
                'invoice_id'     => $invoiceId,
                'amount'         => $inv['total_amount'],
                'payment_method' => $method,
                'ref'            => $reference,
                'notes'          => $notes,
            ]);

            Database::commit();
            return true;
        } catch (Exception $e) {
            Database::rollBack();
            return false;
        }
    }

    /**
     * TÍNH TỔNG DOANH THU THỰC THU (Chỉ cộng hóa đơn status = 'paid')
     */
    public function getTotalRevenue(?string $startDate = null, ?string $endDate = null): float {
        $sql = "SELECT COALESCE(SUM(total_amount), 0) FROM invoices WHERE status = 'paid'";
        $params = [];

        if ($startDate && $endDate) {
            $sql .= " AND DATE(paid_at) BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date']   = $endDate;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float)$stmt->fetchColumn();
    }

    public function countUnpaid(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM invoices WHERE status = 'unpaid'");
        return (int)$stmt->fetchColumn();
    }
}
