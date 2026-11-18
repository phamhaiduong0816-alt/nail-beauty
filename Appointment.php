<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Model: Appointment (Quản lý đặt lịch, kiểm tra trùng lịch & phân công)
 */

require_once APP_PATH . '/helpers/Database.php';

class Appointment {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array {
        $sql = "SELECT a.*, 
                       c.full_name AS customer_name, c.phone AS customer_phone, c.address AS customer_address,
                       u_c.email AS customer_email,
                       s.full_name AS staff_name, s.phone AS staff_phone, s.title AS staff_title,
                       srv.name AS service_name, srv.code AS service_code, srv.duration_minutes, srv.category AS service_category,
                       inv.id AS invoice_id, inv.invoice_code, inv.status AS invoice_status, inv.total_amount AS invoice_amount
                FROM appointments a
                JOIN customers c ON a.customer_id = c.id
                JOIN users u_c ON c.user_id = u_c.id
                JOIN staff s ON a.staff_id = s.id
                JOIN services srv ON a.service_id = srv.id
                LEFT JOIN invoices inv ON inv.appointment_id = a.id
                WHERE a.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByCode(string $code): ?array {
        $stmt = $this->db->prepare("SELECT id FROM appointments WHERE code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        $id = $stmt->fetchColumn();
        return $id ? $this->findById((int)$id) : null;
    }

    public function getAll(array $filters = []): array {
        $sql = "SELECT a.*, 
                       c.full_name AS customer_name, c.phone AS customer_phone,
                       s.full_name AS staff_name,
                       srv.name AS service_name, srv.duration_minutes,
                       inv.id AS invoice_id, inv.invoice_code, inv.status AS invoice_status
                FROM appointments a
                JOIN customers c ON a.customer_id = c.id
                JOIN staff s ON a.staff_id = s.id
                JOIN services srv ON a.service_id = srv.id
                LEFT JOIN invoices inv ON inv.appointment_id = a.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND a.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['date'])) {
            $sql .= " AND a.appointment_date = :date";
            $params['date'] = $filters['date'];
        }

        if (!empty($filters['staff_id'])) {
            $sql .= " AND a.staff_id = :staff_id";
            $params['staff_id'] = $filters['staff_id'];
        }

        if (!empty($filters['customer_id'])) {
            $sql .= " AND a.customer_id = :customer_id";
            $params['customer_id'] = $filters['customer_id'];
        }

        if (!empty($filters['keyword'])) {
            $sql .= " AND (a.code LIKE :kw OR c.full_name LIKE :kw OR c.phone LIKE :kw OR srv.name LIKE :kw)";
            $params['kw'] = "%" . $filters['keyword'] . "%";
        }

        $sql .= " ORDER BY a.appointment_date DESC, a.start_time DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getByCustomer(int $customerId): array {
        return $this->getAll(['customer_id' => $customerId]);
    }

    public function getByStaff(int $staffId, ?string $date = null): array {
        $filters = ['staff_id' => $staffId];
        if ($date) {
            $filters['date'] = $date;
        }
        return $this->getAll($filters);
    }

    /**
     * KIỂM TRA NHÂN VIÊN CÓ CA LÀM VIỆC VÀ NẰM TRONG GIỜ LÀM VIỆC KHÔNG
     */
    public function checkStaffWorkingHours(int $staffId, string $date, string $startTime, string $endTime): bool {
        // Thứ trong tuần: 1 (Thứ 2) -> 7 (Chủ Nhật)
        $dayOfWeek = (int)date('N', strtotime($date));

        $sql = "SELECT * FROM staff_working_hours 
                WHERE staff_id = :staff_id AND day_of_week = :day_of_week AND is_working = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'staff_id'    => $staffId,
            'day_of_week' => $dayOfWeek,
        ]);
        $hours = $stmt->fetch();

        if (!$hours) {
            return false; // Nhân viên nghỉ ngày này
        }

        // Kiểm tra thời gian hẹn phải nằm trong [start_time, end_time] của ca làm việc
        return ($startTime >= $hours['start_time'] && $endTime <= $hours['end_time']);
    }

    /**
     * KIỂM TRA XUNG ĐỘT LỊCH HẸN CỦA NHÂN VIÊN
     * Điều kiện chồng lấn: new_start < existing_end AND new_end > existing_start
     * Chỉ kiểm tra các lịch có hiệu lực: pending, confirmed, in_progress. Bỏ qua cancelled.
     */
    public function checkStaffConflict(int $staffId, string $date, string $startTime, string $endTime, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM appointments 
                WHERE staff_id = :staff_id 
                  AND appointment_date = :date
                  AND status IN ('pending', 'confirmed', 'in_progress')
                  AND (:start_time < end_time AND :end_time > start_time)";
        $params = [
            'staff_id'   => $staffId,
            'date'       => $date,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * TỰ ĐỘNG TÌM NHÂN VIÊN KHẢ DỤNG CHO KHUNG GIỜ
     * Tìm nhân viên active, có ca làm việc và không bị xung đột lịch hẹn
     */
    public function findAvailableStaff(string $date, string $startTime, string $endTime): ?int {
        $dayOfWeek = (int)date('N', strtotime($date));

        $sql = "SELECT s.id 
                FROM staff s
                JOIN staff_working_hours swh ON s.id = swh.staff_id
                WHERE s.status = 'active'
                  AND swh.day_of_week = :day_of_week
                  AND swh.is_working = 1
                  AND swh.start_time <= :start_time
                  AND swh.end_time >= :end_time
                ORDER BY s.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'day_of_week' => $dayOfWeek,
            'start_time'  => $startTime,
            'end_time'    => $endTime,
        ]);
        $candidates = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($candidates as $candId) {
            if (!$this->checkStaffConflict((int)$candId, $date, $startTime, $endTime)) {
                return (int)$candId;
            }
        }

        return null;
    }

    /**
     * TẠO LỊCH HẸN MỚI
     */
    public function create(array $data): int {
        // Sinh mã lịch hẹn duy nhất: APT-YYYYMMDD-RAND4
        $dateCode = str_replace('-', '', $data['appointment_date']);
        $randCode = strtoupper(substr(uniqid(), -4));
        $code = "APT-{$dateCode}-{$randCode}";

        $sql = "INSERT INTO appointments 
                (code, customer_id, service_id, staff_id, appointment_date, start_time, end_time, price_at_booking, status, notes)
                VALUES (:code, :customer_id, :service_id, :staff_id, :appointment_date, :start_time, :end_time, :price_at_booking, :status, :notes)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'code'             => $code,
            'customer_id'      => $data['customer_id'],
            'service_id'       => $data['service_id'],
            'staff_id'         => $data['staff_id'],
            'appointment_date' => $data['appointment_date'],
            'start_time'       => $data['start_time'],
            'end_time'         => $data['end_time'],
            'price_at_booking' => $data['price_at_booking'],
            'status'           => $data['status'] ?? 'pending',
            'notes'            => $data['notes'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * CẬP NHẬT TRẠNG THÁI LỊCH HẸN
     */
    public function updateStatus(int $id, string $status): bool {
        $allowed = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE appointments SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    /**
     * PHÂN CÔNG HOẶC ĐỔI LỊCH (Reschedule)
     */
    public function reschedule(int $id, string $date, string $startTime, string $endTime, int $staffId): bool {
        $stmt = $this->db->prepare("
            UPDATE appointments 
            SET appointment_date = :date, start_time = :start_time, end_time = :end_time, staff_id = :staff_id
            WHERE id = :id
        ");
        return $stmt->execute([
            'date'       => $date,
            'start_time' => $startTime,
            'end_time'   => $endTime,
            'staff_id'   => $staffId,
            'id'         => $id,
        ]);
    }

    /**
     * ĐẾM SỐ LỊCH HẸN HÔM NAY
     */
    public function countToday(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()");
        return (int)$stmt->fetchColumn();
    }

    /**
     * ĐẾM SỐ LỊCH ĐANG CHỜ XÁC NHẬN
     */
    public function countPending(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'");
        return (int)$stmt->fetchColumn();
    }
}
