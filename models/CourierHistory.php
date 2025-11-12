<?php

class CourierHistory {
    public ?int $id;
    public string $courier_name;
    public string $phone_number;
    public int $total_orders;
    public int $total_delivered;
    public int $total_cancelled;
    public string $last_updated;

    public function __construct(
        ?int $id = null,
        string $courier_name = 'steadfast',
        string $phone_number = '',
        int $total_orders = 0,
        int $total_delivered = 0,
        int $total_cancelled = 0,
        string $last_updated = ''
    ) {
        $this->id = $id;
        $this->courier_name = $courier_name;
        $this->phone_number = $phone_number;
        $this->total_orders = $total_orders;
        $this->total_delivered = $total_delivered;
        $this->total_cancelled = $total_cancelled;
        $this->last_updated = $last_updated;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['id'] ?? null,
            $data['courier_name'] ?? 'steadfast',
            $data['phone_number'] ?? '',
            $data['total_orders'] ?? 0,
            $data['total_delivered'] ?? 0,
            $data['total_cancelled'] ?? 0,
            $data['last_updated'] ?? ''
        );
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

    public static function findByPhoneNumber(string $phoneNumber): ?self {
        $db = require __DIR__ . '/../core/db.php';
        $stmt = $db->prepare('SELECT * FROM courier_history WHERE phone_number = ?');
        $stmt->execute([$phoneNumber]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? self::fromArray($data) : null;
    }

    public function save(): void {
        $db = require __DIR__ . '/../core/db.php';

        if ($this->id) {
            $stmt = $db->prepare(
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
            $stmt = $db->prepare(
                'INSERT INTO courier_history (courier_name, phone_number, total_orders, total_delivered, total_cancelled) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $this->courier_name,
                $this->phone_number,
                $this->total_orders,
                $this->total_delivered,
                $this->total_cancelled,
            ]);
            $this->id = (int) $db->lastInsertId();
        }
    }
}
