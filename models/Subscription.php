<?php

class Subscription
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create($user_id, $plan_id)
    {
        // For simplicity, let's set ends_at to a very distant future date
        // In a real application, this would be calculated based on the plan's duration
        $stmt = $this->db->prepare("INSERT INTO subscriptions (user_id, plan_id, starts_at, ends_at) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 100 YEAR))");
        return $stmt->execute([$user_id, $plan_id]);
    }

    public function findByUser($user_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUserPlan($user_id, $new_plan_id)
    {
        $sql = "UPDATE subscriptions SET plan_id = ? WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$new_plan_id, $user_id]);
    }
}