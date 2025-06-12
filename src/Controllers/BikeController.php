<?php

namespace App\Controllers;

use App\Models\Bike;
use App\Repositories\BikeRepository;

class BikeController {
    private BikeRepository $bikeRepository;

    public function __construct(BikeRepository $bikeRepository) {
        $this->bikeRepository = $bikeRepository;
    }

    public function index() {
        $bikes = $this->bikeRepository->findAll();
        return json_encode(array_map(fn($bike) => $bike->toArray(), $bikes));
    }

    public function show(int $id) {
        $bike = $this->bikeRepository->findById($id);
        if (!$bike) {
            http_response_code(404);
            return json_encode(['error' => 'Bike not found']);
        }
        return json_encode($bike->toArray());
    }

    public function store() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $bike = new Bike(
            0,
            $data['type'] ?? 'classic',
            $data['status'] ?? 'available',
            $data['description'] ?? null,
            $data['hourly_rate'] ?? 0.0
        );

        $savedBike = $this->bikeRepository->save($bike);
        http_response_code(201);
        return json_encode($savedBike->toArray());
    }

    public function update(int $id) {
        $bike = $this->bikeRepository->findById($id);
        if (!$bike) {
            http_response_code(404);
            return json_encode(['error' => 'Bike not found']);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['type'])) $bike->setType($data['type']);
        if (isset($data['status'])) $bike->setStatus($data['status']);
        if (isset($data['description'])) $bike->setDescription($data['description']);
        if (isset($data['hourly_rate'])) $bike->setHourlyRate($data['hourly_rate']);

        $updatedBike = $this->bikeRepository->save($bike);
        return json_encode($updatedBike->toArray());
    }

    public function destroy(int $id) {
        $bike = $this->bikeRepository->findById($id);
        if (!$bike) {
            http_response_code(404);
            return json_encode(['error' => 'Bike not found']);
        }

        $this->bikeRepository->delete($id);
        http_response_code(204);
        return '';
    }
} 