<?php

namespace App\Controllers;

use App\Models\Reservation;
use App\Repositories\ReservationRepository;
use App\Repositories\BikeRepository;
use App\Repositories\CustomerRepository;

class ReservationController {
    private ReservationRepository $reservationRepository;
    private BikeRepository $bikeRepository;
    private CustomerRepository $customerRepository;

    public function __construct(
        ReservationRepository $reservationRepository,
        BikeRepository $bikeRepository,
        CustomerRepository $customerRepository
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->bikeRepository = $bikeRepository;
        $this->customerRepository = $customerRepository;
    }

    public function index() {
        $reservations = $this->reservationRepository->findAll();
        return json_encode(array_map(fn($reservation) => $reservation->toArray(), $reservations));
    }

    public function show(int $id) {
        $reservation = $this->reservationRepository->findById($id);
        if (!$reservation) {
            http_response_code(404);
            return json_encode(['error' => 'Reservation not found']);
        }
        return json_encode($reservation->toArray());
    }

    public function store() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate bike and customer exist
        $bike = $this->bikeRepository->findById($data['bike_id'] ?? 0);
        $customer = $this->customerRepository->findById($data['customer_id'] ?? 0);
        
        if (!$bike || !$customer) {
            http_response_code(400);
            return json_encode(['error' => 'Invalid bike or customer ID']);
        }

        $reservation = new Reservation(
            0,
            $data['bike_id'] ?? 0,
            $data['customer_id'] ?? 0,
            new \DateTime($data['start_time'] ?? 'now'),
            new \DateTime($data['end_time'] ?? '+1 day'),
            $data['status'] ?? 'pending',
            $data['notes'] ?? null
        );

        $savedReservation = $this->reservationRepository->save($reservation);
        http_response_code(201);
        return json_encode($savedReservation->toArray());
    }

    public function update(int $id) {
        $reservation = $this->reservationRepository->findById($id);
        if (!$reservation) {
            http_response_code(404);
            return json_encode(['error' => 'Reservation not found']);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['bike_id'])) $reservation->setBikeId($data['bike_id']);
        if (isset($data['customer_id'])) $reservation->setCustomerId($data['customer_id']);
        if (isset($data['start_time'])) $reservation->setStartTime(new \DateTime($data['start_time']));
        if (isset($data['end_time'])) $reservation->setEndTime(new \DateTime($data['end_time']));
        if (isset($data['status'])) $reservation->setStatus($data['status']);
        if (isset($data['notes'])) $reservation->setNotes($data['notes']);

        $updatedReservation = $this->reservationRepository->save($reservation);
        return json_encode($updatedReservation->toArray());
    }

    public function destroy(int $id) {
        $reservation = $this->reservationRepository->findById($id);
        if (!$reservation) {
            http_response_code(404);
            return json_encode(['error' => 'Reservation not found']);
        }

        $this->reservationRepository->delete($id);
        http_response_code(204);
        return '';
    }
} 