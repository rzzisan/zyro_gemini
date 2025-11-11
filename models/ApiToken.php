<?php

class ApiToken
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function generateKey($user_id, $website_id)
    {
        $token = 'zyro_' . bin2hex(random_bytes(32));
        $stmt = $this->db->prepare("INSERT INTO api_tokens (user_id, website_id, token) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $website_id, $token]);
        return $token;
    }

    public function findByWebsite($website_id)
    {
        $stmt = $this->db->prepare("SELECT token FROM api_tokens WHERE website_id = ?");
        $stmt->execute([$website_id]);
        return $stmt->fetchColumn();
    }

    public function deleteByWebsite($website_id)
    {
        $stmt = $this->db->prepare("DELETE FROM api_tokens WHERE website_id = ?");
        return $stmt->execute([$website_id]);
    }

    public function findByToken($token)
    {
        $stmt = $this->db->prepare("SELECT * FROM api_tokens WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateLastUsed($id)
    {
        $stmt = $this->db->prepare("UPDATE api_tokens SET last_used_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
    }
}