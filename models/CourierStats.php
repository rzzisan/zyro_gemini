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

    public function addFraudReport($phoneNumber, $report)
    {
        $stat = $this->findByPhoneNumber($phoneNumber);

        if ($stat) {
            $userReports = $stat['user_reports'] ? json_decode($stat['user_reports'], true) : [];
            $report['report_id'] = uniqid('rep_'); // Use simpler unique ID
            $userReports[] = $report;
            $updatedReports = json_encode($userReports);

            $sql = "UPDATE courier_stats SET user_reports = :user_reports WHERE phone_number = :phone_number";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':user_reports' => $updatedReports,
                ':phone_number' => $phoneNumber
            ]);
        }
        return false;
    }

    public function getReportsByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT phone_number, user_reports FROM courier_stats WHERE user_reports IS NOT NULL");
        $stmt->execute();
        $allStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $userReports = [];
        foreach ($allStats as $stat) {
            $reports = json_decode($stat['user_reports'], true);
            if (is_array($reports)) {
                foreach ($reports as $report) {
                    if (isset($report['user_id']) && $report['user_id'] == $userId) {
                        $report['phone_number'] = $stat['phone_number']; // Add phone number for context
                        $userReports[] = $report;
                    }
                }
            }
        }
        return $userReports;
    }

    public function updateUserReport($userId, $phoneNumber, $reportId, $newCustomerName, $newComplaint)
    {
        $stat = $this->findByPhoneNumber($phoneNumber);
        if (!$stat || empty($stat['user_reports'])) {
            return false;
        }

        $reports = json_decode($stat['user_reports'], true);
        $reportFound = false;
        foreach ($reports as &$report) {
            if (isset($report['report_id']) && $report['report_id'] === $reportId) {
                if (isset($report['user_id']) && $report['user_id'] == $userId) {
                    $report['customer_name'] = $newCustomerName;
                    $report['complaint'] = $newComplaint;
                    $reportFound = true;
                }
                break;
            }
        }

        if ($reportFound) {
            $updatedReports = json_encode($reports);
            $sql = "UPDATE courier_stats SET user_reports = :user_reports WHERE phone_number = :phone_number";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':user_reports' => $updatedReports, ':phone_number' => $phoneNumber]);
        }

        return false;
    }

    public function deleteUserReport($userId, $phoneNumber, $reportId)
    {
        $stat = $this->findByPhoneNumber($phoneNumber);
        if (!$stat || empty($stat['user_reports'])) {
            return false;
        }

        $reports = json_decode($stat['user_reports'], true);
        $reportToDelete = null;
        $reportIndex = -1;

        foreach ($reports as $index => $report) {
            if (isset($report['report_id']) && $report['report_id'] === $reportId) {
                if (isset($report['user_id']) && $report['user_id'] == $userId) {
                    $reportToDelete = $report;
                    $reportIndex = $index;
                }
                break;
            }
        }

        if ($reportToDelete) {
            // Move to trash table
            $trashSql = "INSERT INTO trash_fraud_report (user_id, phone_number, customer_name, complaint, reported_at) VALUES (:user_id, :phone_number, :customer_name, :complaint, :reported_at)";
            $trashStmt = $this->db->prepare($trashSql);
            $trashStmt->execute([
                ':user_id' => $reportToDelete['user_id'],
                ':phone_number' => $phoneNumber,
                ':customer_name' => $reportToDelete['customer_name'],
                ':complaint' => $reportToDelete['complaint'],
                ':reported_at' => $reportToDelete['reported_at']
            ]);

            // Remove from user_reports
            array_splice($reports, $reportIndex, 1);
            $updatedReports = json_encode($reports);
            $sql = "UPDATE courier_stats SET user_reports = :user_reports WHERE phone_number = :phone_number";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':user_reports' => $updatedReports, ':phone_number' => $phoneNumber]);
        }

        return false;
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