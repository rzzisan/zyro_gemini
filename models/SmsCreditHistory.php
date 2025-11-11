<?php
require_once ROOT_PATH . '/core/db.php';

class SmsCreditHistory {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addCreditHistory($user_id, $type, $amount) {
        $stmt = $this->db->prepare("INSERT INTO sms_credit_history (user_id, type, amount) VALUES (?, ?, ?)");
        return $stmt->execute([$user_id, $type, $amount]);
    }

    public function getTotalCredits()
    {
        $stmt = $this->db->query("SELECT SUM(amount) as total FROM sms_credit_history WHERE type = 'credit'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    public function getTotalDebits()
    {
        $stmt = $this->db->query("SELECT SUM(amount) as total FROM sms_credit_history WHERE type = 'debit'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }
}
