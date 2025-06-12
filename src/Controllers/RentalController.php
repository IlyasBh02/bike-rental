<?php

namespace App\Controllers;

use App\Models\Rental;
use App\Repositories\RentalRepository;
use App\Repositories\BikeRepository;
use App\Repositories\CustomerRepository;

class RentalController {
    private RentalRepository $rentalRepository;
    private BikeRepository $bikeRepository;
    private CustomerRepository $customerRepository;

    public function __construct(
        RentalRepository $rentalRepository,
        BikeRepository $bikeRepository,
        CustomerRepository $customerRepository
    ) {
        $this->rentalRepository = $rentalRepository;
        $this->bikeRepository = $bikeRepository;
        $this->customerRepository = $customerRepository;
    }

    public function index() {
        $rentals = $this->rentalRepository->findAll();
        return json_encode(array_map(fn($rental) => $rental->toArray(), $rentals));
    }

    public function show(int $id) {
        $rental = $this->rentalRepository->findById($id);
        if (!$rental) {
            http_response_code(404);
            return json_encode(['error' => 'Rental not found']);
        }
        return json_encode($rental->toArray());
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

        $rental = new Rental(
            0,
            $data['bike_id'] ?? 0,
            $data['customer_id'] ?? 0,
            new \DateTime($data['start_time'] ?? 'now'),
            isset($data['planned_end_time']) ? new \DateTime($data['planned_end_time']) : null,
            null,
            $data['total_amount'] ?? 0.0,
            $data['penalty_amount'] ?? 0.0,
            $data['status'] ?? 'active',
            $data['notes'] ?? null
        );

        $savedRental = $this->rentalRepository->save($rental);
        http_response_code(201);
        return json_encode($savedRental->toArray());
    }

    public function update(int $id) {
        $rental = $this->rentalRepository->findById($id);
        if (!$rental) {
            http_response_code(404);
            return json_encode(['error' => 'Rental not found']);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['bike_id'])) $rental->setBikeId($data['bike_id']);
        if (isset($data['customer_id'])) $rental->setCustomerId($data['customer_id']);
        if (isset($data['start_time'])) $rental->setStartTime(new \DateTime($data['start_time']));
        if (isset($data['planned_end_time'])) $rental->setPlannedEndTime(new \DateTime($data['planned_end_time']));
        if (isset($data['actual_end_time'])) $rental->setActualEndTime(new \DateTime($data['actual_end_time']));
        if (isset($data['total_amount'])) $rental->setTotalAmount($data['total_amount']);
        if (isset($data['penalty_amount'])) $rental->setPenaltyAmount($data['penalty_amount']);
        if (isset($data['status'])) $rental->setStatus($data['status']);
        if (isset($data['notes'])) $rental->setNotes($data['notes']);

        $updatedRental = $this->rentalRepository->save($rental);
        return json_encode($updatedRental->toArray());
    }

    public function destroy(int $id) {
        $rental = $this->rentalRepository->findById($id);
        if (!$rental) {
            http_response_code(404);
            return json_encode(['error' => 'Rental not found']);
        }

        $this->rentalRepository->delete($id);
        http_response_code(204);
        return '';
    }
} 