<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Model: User
 */

require_once APP_PATH . '/helpers/Database.php';

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByUsernameOrEmail(string $login): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1");
        $stmt->execute(['username' => $login, 'email' => $login]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): int {
        $sql = "INSERT INTO users (username, email, password, role, status) 
                VALUES (:username, :email, :password, :role, :status)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role'     => $data['role'] ?? 'CUSTOMER',
            'status'   => $data['status'] ?? 'active',
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updatePassword(int $id, string $newPassword): bool {
        $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
        return $stmt->execute([
            'password' => password_hash($newPassword, PASSWORD_BCRYPT),
            'id'       => $id,
        ]);
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE users SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT id, username, email, role, status, created_at FROM users ORDER BY id DESC");
        return $stmt->fetchAll();
    }
}
