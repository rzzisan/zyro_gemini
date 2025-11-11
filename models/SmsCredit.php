<?php

class SmsCredit
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create($user_id, $initial_balance)
    {
        $stmt = $this->db->prepare("INSERT INTO sms_credits (user_id, balance) VALUES (?, ?)");
        return $stmt->execute([$user_id, $initial_balance]);
    }

    /**
     * Get the current SMS credit balance for a user.
     *
     * @param int $user_id The ID of the user.
     * @return int The current balance, or 0 if not found.
     */
    public function getBalance($user_id)
    {
        $stmt = $this->db->prepare("SELECT balance FROM sms_credits WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $balance = $stmt->fetchColumn();
        return $balance !== false ? (int) $balance : 0;
    }

    public function setBalance($user_id, $amount)
    {
        $stmt = $this->db->prepare("UPDATE sms_credits SET balance = ? WHERE user_id = ?");
        return $stmt->execute([$amount, $user_id]);
    }

    public function addCredits($user_id, $amount_to_add)
    {
        $stmt = $this->db->prepare("UPDATE sms_credits SET balance = balance + ? WHERE user_id = ?");
        return $stmt->execute([$amount_to_add, $user_id]);
    }

    /**
     * Checks if a user has enough SMS credits.
     *
     * @param int $user_id The ID of the user.
     * @param int $credits_needed The number of credits required.
     * @return bool True if the user has enough credits, false otherwise.
     */
    public function hasCredits($user_id, $credits_needed)
    {
        $balance = $this->getBalance($user_id);
        return $balance >= $credits_needed;
    }

    /**
     * Deducts a specified number of SMS credits from a user's balance.
     *
     * @param int $user_id The ID of the user.
     * @param int $credits_to_deduct The number of credits to deduct.
     * @return bool True on successful deduction, false otherwise (e.g., insufficient balance).
     */
    public function deductCredits($user_id, $credits_to_deduct)
    {
        $stmt = $this->db->prepare("UPDATE sms_credits SET balance = balance - ? WHERE user_id = ? AND balance >= ?");
        return $stmt->execute([$credits_to_deduct, $user_id, $credits_to_deduct]);
    }
}
