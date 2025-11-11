<?php
require_once ROOT_PATH . '/core/db.php';

class SmsHistory {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addSmsHistory($user_id, $to_number, $message, $sms_count, $credit_deducted) {
        $stmt = $this->db->prepare("INSERT INTO sms_history (user_id, to_number, message, sms_count, credit_deducted) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$user_id, $to_number, $message, $sms_count, $credit_deducted]);
    }

    public function getSmsHistoryByUser($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM sms_history WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSmsHistoryByUserPaginated($user_id, $limit, $offset, $search_term = '', $start_date = '', $end_date = '') {
        $sql = "SELECT * FROM sms_history WHERE user_id = :user_id";
        $params = [':user_id' => $user_id];

        if (!empty($search_term)) {
            $sql .= " AND (to_number LIKE :search_term1 OR message LIKE :search_term2)";
            $params[':search_term1'] = "%$search_term%";
            $params[':search_term2'] = "%$search_term%";
        }

        if (!empty($start_date)) {
            $sql .= " AND created_at >= :start_date";
            $params[':start_date'] = $start_date;
        }

        if (!empty($end_date)) {
            $sql .= " AND created_at < :end_date";
            $params[':end_date'] = date('Y-m-d', strtotime($end_date . ' +1 day'));
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalSmsHistoryCountByUser($user_id, $search_term = '', $start_date = '', $end_date = '') {
        $sql = "SELECT COUNT(*) FROM sms_history WHERE user_id = :user_id";
        $params = [':user_id' => $user_id];

        if (!empty($search_term)) {
            $sql .= " AND (to_number LIKE :search_term1 OR message LIKE :search_term2)";
            $params[':search_term1'] = "%$search_term%";
            $params[':search_term2'] = "%$search_term%";
        }

        if (!empty($start_date)) {
            $sql .= " AND created_at >= :start_date";
            $params[':start_date'] = $start_date;
        }

        if (!empty($end_date)) {
            $sql .= " AND created_at < :end_date";
            $params[':end_date'] = date('Y-m-d', strtotime($end_date . ' +1 day'));
        }

        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
