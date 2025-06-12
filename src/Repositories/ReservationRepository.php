<?php

namespace App\Repositories;

use App\Models\Reservation;
use PDO;

class ReservationRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findAll(): array {
        $stmt = $this->db->query('SELECT * FROM reservations');
        $reservations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $reservations[] = $this->createReservationFromRow($row);
        }
        return $reservations;
    }

    public function findById(int $id): ?Reservation {
        $stmt = $this->db->prepare('SELECT * FROM reservations WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->createReservationFromRow($row) : null;
    }

    public function save(Reservation $reservation): Reservation {
        if ($reservation->getId() === 0) {
            return $this->insert($reservation);
        }
        return $this->update($reservation);
    }

    private function insert(Reservation $reservation): Reservation {
        $stmt = $this->db->prepare('
            INSERT INTO reservations (customer_id, bike_id, start_time, end_time, status)
            VALUES (:customer_id, :bike_id, :start_time, :end_time, :status)
        ');

        $stmt->execute([
            'customer_id' => $reservation->getCustomerId(),
            'bike_id' => $reservation->getBikeId(),
            'start_time' => $reservation->getStartTime()->format('Y-m-d H:i:s'),
            'end_time' => $reservation->getEndTime()->format('Y-m-d H:i:s'),
            'status' => $reservation->getStatus()
        ]);

        $reservation->setId((int) $this->db->lastInsertId());
        return $reservation;
    }

    private function update(Reservation $reservation): Reservation {
        $stmt = $this->db->prepare('
            UPDATE reservations
            SET customer_id = :customer_id,
                bike_id = :bike_id,
                start_time = :start_time,
                end_time = :end_time,
                status = :status
            WHERE id = :id
        ');

        $stmt->execute([
            'id' => $reservation->getId(),
            'customer_id' => $reservation->getCustomerId(),
            'bike_id' => $reservation->getBikeId(),
            'start_time' => $reservation->getStartTime()->format('Y-m-d H:i:s'),
            'end_time' => $reservation->getEndTime()->format('Y-m-d H:i:s'),
            'status' => $reservation->getStatus()
        ]);

        return $reservation;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM reservations WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    private function createReservationFromRow(array $row): Reservation {
        return new Reservation(
            (int) $row['id'],
            (int) $row['customer_id'],
            (int) $row['bike_id'],
            new \DateTime($row['start_time']),
            new \DateTime($row['end_time']),
            $row['status']
        );
    }
} 