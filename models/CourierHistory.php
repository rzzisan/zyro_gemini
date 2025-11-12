<?php

class CourierHistory {
    private $db;
    public ?int $id;
    public string $courier_name;
    public string $phone_number;
    public int $total_orders;
    public int $total_delivered;
    public int $total_cancelled;
    public string $last_updated;

    public function __construct($db) {
        $this->db = $db;
    }

    public function fromArray(array $data): self {
        $instance = new self($this->db);
        $instance->id = $data['id'] ?? null;
        $instance->courier_name = $data['courier_name'] ?? 'steadfast';
        $instance->phone_number = $data['phone_number'] ?? '';
        $instance->total_orders = $data['total_orders'] ?? 0;
        $instance->total_delivered = $data['total_delivered'] ?? 0;
        $instance->total_cancelled = $data['total_cancelled'] ?? 0;
        $instance->last_updated = $data['last_updated'] ?? '';
        return $instance;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'courier_name' => $this->courier_name,
            'phone_number' => $this->phone_number,
            'total_orders' => $this->total_orders,
            'total_delivered' => $this->total_delivered,
            'total_cancelled' => $this->total_cancelled,
            'last_updated' => $this->last_updated,
        ];
    }

    public function findByPhoneNumber(string $phoneNumber): ?self {
        $stmt = $this->db->prepare('SELECT * FROM courier_history WHERE phone_number = ?');
        $stmt->execute([$phoneNumber]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->fromArray($data) : null;
    }

    public function save(): void {
        if ($this->id) {
            $stmt = $this->db->prepare(
                'UPDATE courier_history SET courier_name = ?, phone_number = ?, total_orders = ?, total_delivered = ?, total_cancelled = ? WHERE id = ?'
            );
            $stmt->execute([
                $this->courier_name,
                $this->phone_number,
                $this->total_orders,
                $this->total_delivered,
                $this->total_cancelled,
                $this->id,
            ]);
        } else {
            $stmt = $this->db->prepare(
                'INSERT INTO courier_history (courier_name, phone_number, total_orders, total_delivered, total_cancelled) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $this->courier_name,
                $this->phone_number,
                $this->total_orders,
                $this->total_delivered,
                $this->total_cancelled,
            ]);
            $this->id = (int) $this->db->lastInsertId();
        }
    }

    public function getCourierHistoryPaginated(int $limit, int $offset, string $searchTerm = ''): array {
        $sql = 'SELECT * FROM courier_history';
        $params = [];

        if ($searchTerm) {
            $sql .= ' WHERE phone_number LIKE ?';
            $params[] = '%' . $searchTerm . '%';
        }

        $sql .= ' ORDER BY last_updated DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $this->fromArray($row);
        }

        return $results;
    }

    public function getTotalCourierHistoryCount(string $searchTerm = ''): int {
        $sql = 'SELECT COUNT(*) FROM courier_history';
        $params = [];

        if ($searchTerm) {
            $sql .= ' WHERE phone_number LIKE ?';
            $params[] = '%' . $searchTerm . '%';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }
}
