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
    public function getTotalCourierStatsCount($searchTerm = '')
    {
        $sql = "SELECT COUNT(*) FROM courier_stats";
        $params = [];
        if (!empty($searchTerm)) {
            $sql .= " WHERE phone_number LIKE ?";
            $params[] = '%' . $searchTerm . '%';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getCourierStatsPaginated($page, $rowsPerPage, $searchTerm = '')
    {
        $offset = ($page - 1) * $rowsPerPage;
        $sql = "SELECT * FROM courier_stats";
        
        if (!empty($searchTerm)) {
            $sql .= " WHERE phone_number LIKE :searchTerm";
        }
        $sql .= " ORDER BY last_updated_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        if (!empty($searchTerm)) {
            $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', (int) $rowsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}