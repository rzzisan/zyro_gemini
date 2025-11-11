<?php

class UsageLog
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Records a specific usage event for a user.
     *
     * @param int    $user_id      The user's ID.
     * @param string $service_type The type of service used (e.g., 'FRAUD_CHECK').
     * @param string $details      Specific details of the usage (e.g., the phone number checked).
     * @return bool True on success, false on failure.
     */
    public function recordUsage($user_id, $service_type, $details)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO usage_logs (user_id, service_type, details) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$user_id, $service_type, $details]);
    }

    /**
     * Gets the count of unique service usages for a user on the current day.
     *
     * @param int    $user_id      The user's ID.
     * @param string $service_type The type of service to count.
     * @return int The count of unique usages.
     */
    public function getDailyUniqueUsageCount($user_id, $service_type)
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT details) FROM usage_logs WHERE user_id = ? AND service_type = ? AND DATE(created_at) = CURDATE()"
        );
        $stmt->execute([$user_id, $service_type]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Checks if a specific usage detail has already been logged for a user today.
     *
     * @param int    $user_id      The user's ID.
     * @param string $service_type The type of service.
     * @param string $details      The specific detail to check.
     * @return bool True if already checked today, false otherwise.
     */
    public function hasCheckedToday($user_id, $service_type, $details)
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM usage_logs WHERE user_id = ? AND service_type = ? AND details = ? AND DATE(created_at) = CURDATE()"
        );
        $stmt->execute([$user_id, $service_type, $details]);
        return $stmt->fetchColumn() > 0;
    }
}
