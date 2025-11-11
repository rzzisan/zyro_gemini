<?php

class Website
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create($user_id, $domain)
    {
        $stmt = $this->db->prepare("INSERT INTO websites (user_id, domain) VALUES (?, ?)");
        $stmt->execute([$user_id, $domain]);
        return $this->db->lastInsertId();
    }

    public function findByUser($user_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM websites WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM websites WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete($website_id, $user_id)
    {
        $stmt = $this->db->prepare("DELETE FROM websites WHERE id = ? AND user_id = ?");
        return $stmt->execute([$website_id, $user_id]);
    }
}