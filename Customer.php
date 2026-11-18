<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Model: Customer
 */

require_once APP_PATH . '/helpers/Database.php';

class Customer {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array {
        $sql = "SELECT c.*, u.username, u.email, u.status AS user_status, u.created_at AS registered_at
                FROM customers c
                JOIN users u ON c.user_id = u.id
                WHERE c.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByUserId(int $userId): ?array {
        $sql = "SELECT c.*, u.username, u.email 
                FROM customers c
                JOIN users u ON c.user_id = u.id
                WHERE c.user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAll(?string $keyword = null): array {
        $sql = "SELECT c.*, u.username, u.email, u.status AS user_status,
                       (SELECT COUNT(*) FROM appointments a WHERE a.customer_id = c.id) AS total_bookings
                FROM customers c
                JOIN users u ON c.user_id = u.id";
        $params = [];

        if ($keyword) {
            $sql .= " WHERE c.full_name LIKE :kw OR c.phone LIKE :kw OR u.email LIKE :kw";
            $params['kw'] = "%$keyword%";
        }

        $sql .= " ORDER BY c.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $sql = "INSERT INTO customers (user_id, full_name, phone, address, notes)
                VALUES (:user_id, :full_name, :phone, :address, :notes)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id'   => $data['user_id'],
            'full_name' => $data['full_name'],
            'phone'     => $data['phone'],
            'address'   => $data['address'] ?? null,
            'notes'     => $data['notes'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE customers 
                SET full_name = :full_name, phone = :phone, address = :address, notes = :notes
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'full_name' => $data['full_name'],
            'phone'     => $data['phone'],
            'address'   => $data['address'] ?? null,
            'notes'     => $data['notes'] ?? null,
            'id'        => $id,
        ]);
    }

    public function countAll(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    }
}
