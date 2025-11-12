<?php

class CourierStats
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findByPhoneNumber($phoneNumber)
    {
        $stmt = $this->db->prepare("SELECT * FROM courier_stats WHERE phone_number = ?");
        $stmt->execute([$phoneNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function upsert($data)
    {
        $sql = "INSERT INTO courier_stats (courier_name, phone_number, total_parcels, total_delivered, total_cancelled, total_fraud_reports, last_updated_at) 
                VALUES (:courier_name, :phone_number, :total_parcels, :total_delivered, :total_cancelled, :total_fraud_reports, NOW())
                ON DUPLICATE KEY UPDATE 
                total_parcels = VALUES(total_parcels), 
                total_delivered = VALUES(total_delivered), 
                total_cancelled = VALUES(total_cancelled), 
                total_fraud_reports = VALUES(total_fraud_reports),
                last_updated_at = NOW()";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':courier_name' => $data['courier_name'],
            ':phone_number' => $data['phone_number'],
            ':total_parcels' => $data['total_parcels'],
            ':total_delivered' => $data['total_delivered'],
            ':total_cancelled' => $data['total_cancelled'],
            ':total_fraud_reports' => $data['total_fraud_reports']
        ]);
    }
}