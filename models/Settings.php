<?php
require_once ROOT_PATH . '/core/db.php';

class Settings {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function get($key) {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['setting_value'] : null;
    }

    public function set($key, $value) {
        $stmt = $this->db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        return $stmt->execute([$key, $value, $value]);
    }
}
