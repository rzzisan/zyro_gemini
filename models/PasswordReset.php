<?php

class PasswordReset
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create($userId, $otp)
    {
        $stmt = $this->db->prepare("INSERT INTO password_resets (user_id, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
        return $stmt->execute([$userId, $otp]);
    }

    public function verify($userId, $otp)
    {
        error_log("Verifying OTP for User ID: $userId, OTP: $otp");
        $stmt = $this->db->prepare("SELECT id FROM password_resets WHERE user_id = ? AND otp = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$userId, $otp]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteUserOtps($userId)
    {
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
}
