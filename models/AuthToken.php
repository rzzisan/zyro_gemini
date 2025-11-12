<?php

require_once ROOT_PATH . '/core/db.php';

class AuthToken
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function create($userId, $token, $expiresAt)
    {
        $stmt = $this->db->prepare("INSERT INTO auth_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $token, $expiresAt]);
    }

    public function findByToken($token)
    {
        $stmt = $this->db->prepare("SELECT * FROM auth_tokens WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete($token)
    {
        $stmt = $this->db->prepare("DELETE FROM auth_tokens WHERE token = ?");
        return $stmt->execute([$token]);
    }

    public function deleteByUserId($userId)
    {
        $stmt = $this->db->prepare("DELETE FROM auth_tokens WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
}
