<?php

class CourierCache
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Finds courier data in the cache by phone number.
     *
     * @param string $phone_number The phone number to search for.
     * @return array|false The cached data as an associative array, or false if not found.
     */
    public function find($phone_number)
    {
        $stmt = $this->db->prepare("SELECT * FROM courier_cache WHERE phone_number = ?");
        $stmt->execute([$phone_number]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Saves or updates courier data in the cache.
     *
     * @param string $phone_number The phone number.
     * @param array  $data         The courier data to store (will be JSON encoded).
     * @param float  $success_rate The success rate associated with the courier.
     * @return bool True on success, false on failure.
     */
    public function save($phone_number, $data, $success_rate)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO courier_cache (phone_number, courier_data, success_rate, last_updated) 
             VALUES (?, ?, ?, NOW()) 
             ON DUPLICATE KEY UPDATE courier_data = ?, success_rate = ?, last_updated = NOW()"
        );
        $json_data = json_encode($data);
        return $stmt->execute([$phone_number, $json_data, $success_rate, $json_data, $success_rate]);
    }

    /**
     * Retrieves the oldest cached entries.
     *
     * @param int $limit The maximum number of entries to retrieve.
     * @return array An array of the oldest cached entries.
     */
    public function getOldest($limit = 500)
    {
        $stmt = $this->db->prepare("SELECT * FROM courier_cache ORDER BY last_updated ASC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}