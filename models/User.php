<?php

class User
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getTotalAssignedCredits()
    {
        $stmt = $this->db->query("SELECT SUM(balance) as total_credits FROM sms_credits");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_credits'] ?? 0;
    }


    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT u.*, sc.balance as sms_balance, u.phone_number, u.district, u.upazila 
                                   FROM users u 
                                   LEFT JOIN sms_credits sc ON u.id = sc.user_id 
                                   WHERE u.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll($limit = 10, $offset = 0)
    {
        $sql = "SELECT 
                    u.id, 
                    u.name, 
                    u.email, 
                    u.role,
                    u.created_at,
                    p.name as plan_name,
                    p.daily_courier_limit,
                    sc.balance as sms_balance,
                    COUNT(w.id) as website_count
                FROM users u
                LEFT JOIN subscriptions s ON u.id = s.user_id
                LEFT JOIN plans p ON s.plan_id = p.id
                LEFT JOIN sms_credits sc ON u.id = sc.user_id
                LEFT JOIN websites w ON u.id = w.user_id
                GROUP BY u.id, u.name, u.email, u.role, u.created_at, p.name, p.daily_courier_limit, sc.balance
                ORDER BY u.id ASC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        return $stmt->fetchColumn();
    }

    public function getUserDetails($id)
    {
        $sql = "SELECT 
                    u.id, 
                    u.name, 
                    u.email, 
                    u.role,
                    u.created_at,
                    u.phone_number,
                    u.district,
                    u.upazila,
                    p.name as plan_name,
                    sc.balance as balance
                FROM users u
                LEFT JOIN subscriptions s ON u.id = s.user_id
                LEFT JOIN plans p ON s.plan_id = p.id
                LEFT JOIN sms_credits sc ON u.id = sc.user_id
                WHERE u.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: false;
    }

    public function createAdmin($name, $email, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        return $stmt->execute([$name, $email, $hashedPassword]);
    }

    public function createUser($name, $email, $password, $role = 'user')
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword, $role]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE users SET name = ?, email = ?, role = ?, phone_number = ?, district = ?, upazila = ? WHERE id = ?";
        $params = [
            $data['name'],
            $data['email'],
            $data['role'],
            $data['phone_number'],
            $data['district'],
            $data['upazila'],
            $id
        ];

        if (!empty($data['password'])) {
            $sql = "UPDATE users SET name = ?, email = ?, role = ?, password = ?, phone_number = ?, district = ?, upazila = ? WHERE id = ?";
            $params = [
                $data['name'],
                $data['email'],
                $data['role'],
                password_hash($data['password'], PASSWORD_BCRYPT),
                $data['phone_number'],
                $data['district'],
                $data['upazila'],
                $id
            ];
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateProfile($id, $name, $email, $phone_number, $district, $upazila)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Email already in use
        }

        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, phone_number = ?, district = ?, upazila = ? WHERE id = ?");
        return $stmt->execute([$name, $email, $phone_number, $district, $upazila, $id]);
    }

    public function updatePassword($id, $hashedPassword)
    {
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $id]);
    }

    public function delete($id)
    {
        try {
            $this->db->beginTransaction();

            // Delete from related tables
            if (!$this->db->prepare("DELETE FROM subscriptions WHERE user_id = ?")->execute([$id])) {
                throw new Exception("Failed to delete from subscriptions");
            }
            if (!$this->db->prepare("DELETE FROM sms_credits WHERE user_id = ?")->execute([$id])) {
                throw new Exception("Failed to delete from sms_credits");
            }
            if (!$this->db->prepare("DELETE FROM usage_logs WHERE user_id = ?")->execute([$id])) {
                throw new Exception("Failed to delete from usage_logs");
            }
            if (!$this->db->prepare("DELETE FROM api_tokens WHERE user_id = ?")->execute([$id])) {
                throw new Exception("Failed to delete from api_tokens");
            }
            if (!$this->db->prepare("DELETE FROM websites WHERE user_id = ?")->execute([$id])) {
                throw new Exception("Failed to delete from websites");
            }

            // Finally, delete the user
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            if (!$stmt->execute([$id])) {
                throw new Exception("Failed to delete user");
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            // Optionally log the error
            error_log("User deletion failed: " . $e->getMessage());
            return false;
        }
    }

    public function getAllUsersAsMap()
    {
        $stmt = $this->db->query("SELECT id, name FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user['id']] = $user['name'];
        }
        return $userMap;
    }
}