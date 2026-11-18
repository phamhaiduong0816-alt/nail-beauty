<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Model: Staff
 */

require_once APP_PATH . '/helpers/Database.php';

class Staff {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array {
        $sql = "SELECT s.*, u.username, u.email, u.status AS account_status
                FROM staff s
                JOIN users u ON s.user_id = u.id
                WHERE s.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByUserId(int $userId): ?array {
        $sql = "SELECT s.*, u.username, u.email 
                FROM staff s
                JOIN users u ON s.user_id = u.id
                WHERE s.user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAll(?string $status = null): array {
        $sql = "SELECT s.*, u.username, u.email,
                       (SELECT COUNT(*) FROM appointments a WHERE a.staff_id = s.id AND a.status = 'completed') AS completed_jobs
                FROM staff s
                JOIN users u ON s.user_id = u.id";
        $params = [];

        if ($status) {
            $sql .= " WHERE s.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY s.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getActiveStaff(): array {
        return $this->getAll('active');
    }

    public function create(array $data): int {
        $sql = "INSERT INTO staff (user_id, full_name, phone, title, bio, avatar, status)
                VALUES (:user_id, :full_name, :phone, :title, :bio, :avatar, :status)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id'   => $data['user_id'],
            'full_name' => $data['full_name'],
            'phone'     => $data['phone'],
            'title'     => $data['title'] ?? 'Kỹ thuật viên Nail & Spa',
            'bio'       => $data['bio'] ?? null,
            'avatar'    => $data['avatar'] ?? null,
            'status'    => $data['status'] ?? 'active',
        ]);
        $staffId = (int)$this->db->lastInsertId();

        // Tự động khởi tạo lịch làm việc mặc định 7 ngày trong tuần (08:30 - 20:00)
        $this->initDefaultWorkingHours($staffId);

        return $staffId;
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE staff 
                SET full_name = :full_name, phone = :phone, title = :title, 
                    bio = :bio, status = :status
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'full_name' => $data['full_name'],
            'phone'     => $data['phone'],
            'title'     => $data['title'],
            'bio'       => $data['bio'] ?? null,
            'status'    => $data['status'] ?? 'active',
            'id'        => $id,
        ]);
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE staff SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function getWorkingHours(int $staffId): array {
        $stmt = $this->db->prepare("SELECT * FROM staff_working_hours WHERE staff_id = :staff_id ORDER BY day_of_week ASC");
        $stmt->execute(['staff_id' => $staffId]);
        return $stmt->fetchAll();
    }

    public function saveWorkingHours(int $staffId, array $days): bool {
        $stmt = $this->db->prepare("
            INSERT INTO staff_working_hours (staff_id, day_of_week, start_time, end_time, is_working)
            VALUES (:staff_id, :day_of_week, :start_time, :end_time, :is_working)
            ON DUPLICATE KEY UPDATE 
                start_time = VALUES(start_time),
                end_time = VALUES(end_time),
                is_working = VALUES(is_working)
        ");

        foreach ($days as $day => $info) {
            $stmt->execute([
                'staff_id'    => $staffId,
                'day_of_week' => (int)$day,
                'start_time'  => $info['start_time'] ?? '08:30:00',
                'end_time'    => $info['end_time'] ?? '20:00:00',
                'is_working'  => !empty($info['is_working']) ? 1 : 0,
            ]);
        }
        return true;
    }

    private function initDefaultWorkingHours(int $staffId): void {
        $stmt = $this->db->prepare("
            INSERT INTO staff_working_hours (staff_id, day_of_week, start_time, end_time, is_working)
            VALUES (:staff_id, :day_of_week, '08:30:00', '20:00:00', :is_working)
        ");
        for ($d = 1; $d <= 7; $d++) {
            $isWorking = ($d === 7) ? 0 : 1; // Chủ nhật mặc định nghỉ hoặc theo sắp xếp
            $stmt->execute([
                'staff_id'    => $staffId,
                'day_of_week' => $d,
                'is_working'  => $isWorking
            ]);
        }
    }

    public function countActive(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM staff WHERE status = 'active'")->fetchColumn();
    }
}
