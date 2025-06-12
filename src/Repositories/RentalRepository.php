<?php

namespace App\Repositories;

use App\Models\Rental;
use PDO;

class RentalRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findAll(): array {
        $stmt = $this->db->query('SELECT * FROM rentals ORDER BY id');
        $rentals = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rentals[] = $this->createRentalFromRow($row);
        }
        return $rentals;
    }

    public function findById(int $id): ?Rental {
        $stmt = $this->db->prepare('SELECT * FROM rentals WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->createRentalFromRow($row) : null;
    }

    public function findByBikeId(int $bikeId): array {
        $stmt = $this->db->prepare('SELECT * FROM rentals WHERE bike_id = :bike_id ORDER BY start_time DESC');
        $stmt->execute(['bike_id' => $bikeId]);
        $rentals = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rentals[] = $this->createRentalFromRow($row);
        }
        return $rentals;
    }

    public function findByCustomerId(int $customerId): array {
        $stmt = $this->db->prepare('SELECT * FROM rentals WHERE customer_id = :customer_id ORDER BY start_time DESC');
        $stmt->execute(['customer_id' => $customerId]);
        $rentals = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rentals[] = $this->createRentalFromRow($row);
        }
        return $rentals;
    }

    public function findActive(): array {
        $stmt = $this->db->prepare('SELECT * FROM rentals WHERE status = :status ORDER BY start_time DESC');
        $stmt->execute(['status' => 'active']);
        $rentals = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rentals[] = $this->createRentalFromRow($row);
        }
        return $rentals;
    }

    public function save(Rental $rental): Rental {
        if ($rental->getId() === 0) {
            return $this->insert($rental);
        }
        return $this->update($rental);
    }

    private function insert(Rental $rental): Rental {
        $stmt = $this->db->prepare('
            INSERT INTO rentals (
                bike_id,
                customer_id,
                start_time,
                planned_end_time,
                actual_end_time,
                total_amount,
                penalty_amount,
                status,
                notes
            ) VALUES (
                :bike_id,
                :customer_id,
                :start_time,
                :planned_end_time,
                :actual_end_time,
                :total_amount,
                :penalty_amount,
                :status,
                :notes
            )
        ');

        $stmt->execute([
            'bike_id' => $rental->getBikeId(),
            'customer_id' => $rental->getCustomerId(),
            'start_time' => $rental->getStartTime()->format('Y-m-d H:i:s'),
            'planned_end_time' => $rental->getPlannedEndTime()?->format('Y-m-d H:i:s'),
            'actual_end_time' => $rental->getActualEndTime()?->format('Y-m-d H:i:s'),
            'total_amount' => $rental->getTotalAmount(),
            'penalty_amount' => $rental->getPenaltyAmount(),
            'status' => $rental->getStatus(),
            'notes' => $rental->getNotes()
        ]);

        $rental->setId((int) $this->db->lastInsertId());
        return $rental;
    }

    private function update(Rental $rental): Rental {
        $stmt = $this->db->prepare('
            UPDATE rentals
            SET bike_id = :bike_id,
                customer_id = :customer_id,
                start_time = :start_time,
                planned_end_time = :planned_end_time,
                actual_end_time = :actual_end_time,
                total_amount = :total_amount,
                penalty_amount = :penalty_amount,
                status = :status,
                notes = :notes
            WHERE id = :id
        ');

        $stmt->execute([
            'id' => $rental->getId(),
            'bike_id' => $rental->getBikeId(),
            'customer_id' => $rental->getCustomerId(),
            'start_time' => $rental->getStartTime()->format('Y-m-d H:i:s'),
            'planned_end_time' => $rental->getPlannedEndTime()?->format('Y-m-d H:i:s'),
            'actual_end_time' => $rental->getActualEndTime()?->format('Y-m-d H:i:s'),
            'total_amount' => $rental->getTotalAmount(),
            'penalty_amount' => $rental->getPenaltyAmount(),
            'status' => $rental->getStatus(),
            'notes' => $rental->getNotes()
        ]);

        return $rental;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM rentals WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    private function createRentalFromRow(array $row): Rental {
        $rental = new Rental(
            (int) $row['id'],
            (int) $row['bike_id'],
            (int) $row['customer_id'],
            new \DateTime($row['start_time']),
            $row['planned_end_time'] ? new \DateTime($row['planned_end_time']) : null,
            $row['actual_end_time'] ? new \DateTime($row['actual_end_time']) : null,
            (float) $row['total_amount'],
            (float) $row['penalty_amount'],
            $row['status'],
            $row['notes']
        );
        return $rental;
    }
} 