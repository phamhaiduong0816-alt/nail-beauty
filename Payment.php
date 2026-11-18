<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Model: Payment
 */

require_once APP_PATH . '/helpers/Database.php';

class Payment {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getByInvoiceId(int $invoiceId): array {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE invoice_id = :iid ORDER BY id DESC");
        $stmt->execute(['iid' => $invoiceId]);
        return $stmt->fetchAll();
    }
}
