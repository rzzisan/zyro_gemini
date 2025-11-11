<?php

class Plan
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll()
    {
        $stmt = $this->db->query("SELECT * FROM plans ORDER BY price ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM plans WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO plans (name, price, daily_courier_limit, sms_credit_bonus) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['price'],
            $data['daily_courier_limit'],
            $data['sms_credit_bonus']
        ]);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE plans SET name = ?, price = ?, daily_courier_limit = ?, sms_credit_bonus = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['price'],
            $data['daily_courier_limit'],
            $data['sms_credit_bonus'],
            $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM plans WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function findByName($name)
    {
        $stmt = $this->db->prepare("SELECT * FROM plans WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}