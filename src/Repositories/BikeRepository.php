<?php

namespace App\Repositories;

use App\Models\Bike;
use PDO;

class BikeRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findAll(): array {
        $stmt = $this->db->query('SELECT * FROM bikes ORDER BY id');
        $bikes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bikes[] = $this->createBikeFromRow($row);
        }
        return $bikes;
    }

    public function findById(int $id): ?Bike {
        $stmt = $this->db->prepare('SELECT * FROM bikes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->createBikeFromRow($row) : null;
    }

    public function findByStatus(string $status): array {
        $stmt = $this->db->prepare('SELECT * FROM bikes WHERE status = :status ORDER BY id');
        $stmt->execute(['status' => $status]);
        $bikes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bikes[] = $this->createBikeFromRow($row);
        }
        return $bikes;
    }

    public function findByType(string $type): array {
        $stmt = $this->db->prepare('SELECT * FROM bikes WHERE type = :type ORDER BY id');
        $stmt->execute(['type' => $type]);
        $bikes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bikes[] = $this->createBikeFromRow($row);
        }
        return $bikes;
    }

    public function save(Bike $bike): Bike {
        if ($bike->getId() === 0) {
            return $this->insert($bike);
        }
        return $this->update($bike);
    }

    private function insert(Bike $bike): Bike {
        $stmt = $this->db->prepare('
            INSERT INTO bikes (type, status, description, hourly_rate)
            VALUES (:type, :status, :description, :hourly_rate)
        ');

        $stmt->execute([
            'type' => $bike->getType(),
            'status' => $bike->getStatus(),
            'description' => $bike->getDescription(),
            'hourly_rate' => $bike->getHourlyRate()
        ]);

        $bike->setId((int) $this->db->lastInsertId());
        return $bike;
    }

    private function update(Bike $bike): Bike {
        $stmt = $this->db->prepare('
            UPDATE bikes
            SET type = :type,
                status = :status,
                description = :description,
                hourly_rate = :hourly_rate
            WHERE id = :id
        ');

        $stmt->execute([
            'id' => $bike->getId(),
            'type' => $bike->getType(),
            'status' => $bike->getStatus(),
            'description' => $bike->getDescription(),
            'hourly_rate' => $bike->getHourlyRate()
        ]);

        return $bike;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM bikes WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    private function createBikeFromRow(array $row): Bike {
        return new Bike(
            (int) $row['id'],
            $row['type'],
            $row['status'],
            $row['description'],
            (float) $row['hourly_rate']
        );
    }
} 