<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Model: Service
 */

require_once APP_PATH . '/helpers/Database.php';

class Service {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM services WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByCode(string $code): ?array {
        $stmt = $this->db->prepare("SELECT * FROM services WHERE code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAll(?string $keyword = null, ?string $category = null, ?bool $activeOnly = null): array {
        $sql = "SELECT s.*, 
                       (SELECT COUNT(*) FROM appointments a WHERE a.service_id = s.id) AS booking_count
                FROM services s WHERE 1=1";
        $params = [];

        if ($activeOnly !== null) {
            $sql .= " AND s.is_active = :is_active";
            $params['is_active'] = $activeOnly ? 1 : 0;
        }

        if ($category) {
            $sql .= " AND s.category = :category";
            $params['category'] = $category;
        }

        if ($keyword) {
            $sql .= " AND (s.name LIKE :kw OR s.code LIKE :kw OR s.description LIKE :kw)";
            $params['kw'] = "%$keyword%";
        }

        $sql .= " ORDER BY s.is_active DESC, s.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getActiveServices(): array {
        return $this->getAll(null, null, true);
    }

    public function getCategories(): array {
        $stmt = $this->db->query("SELECT DISTINCT category FROM services WHERE is_active = 1 ORDER BY category ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function create(array $data): int {
        $sql = "INSERT INTO services (code, name, category, description, price, duration_minutes, image_url, is_active)
                VALUES (:code, :name, :category, :description, :price, :duration_minutes, :image_url, :is_active)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'code'             => $data['code'],
            'name'             => $data['name'],
            'category'         => $data['category'],
            'description'      => $data['description'] ?? null,
            'price'            => (float)$data['price'],
            'duration_minutes' => (int)$data['duration_minutes'],
            'image_url'        => $data['image_url'] ?? 'service-gel.svg',
            'is_active'        => !empty($data['is_active']) ? 1 : 0,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE services 
                SET code = :code, name = :name, category = :category, 
                    description = :description, price = :price, 
                    duration_minutes = :duration_minutes, 
                    image_url = :image_url, is_active = :is_active
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'code'             => $data['code'],
            'name'             => $data['name'],
            'category'         => $data['category'],
            'description'      => $data['description'] ?? null,
            'price'            => (float)$data['price'],
            'duration_minutes' => (int)$data['duration_minutes'],
            'image_url'        => $data['image_url'] ?? 'service-gel.svg',
            'is_active'        => !empty($data['is_active']) ? 1 : 0,
            'id'               => $id,
        ]);
    }

    public function toggleActive(int $id): bool {
        $stmt = $this->db->prepare("UPDATE services SET is_active = IF(is_active = 1, 0, 1) WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function countActive(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM services WHERE is_active = 1")->fetchColumn();
    }
}
