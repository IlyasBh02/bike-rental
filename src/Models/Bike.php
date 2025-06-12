<?php

namespace App\Models;

class Bike {
    private int $id;
    private string $type; // 'classic', 'electric', 'cargo'
    private string $status; // 'available', 'maintenance', 'rented'
    private ?string $description;
    private float $hourlyRate;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        int $id = 0,
        string $type = 'classic',
        string $status = 'available',
        ?string $description = null,
        float $hourlyRate = 0.0
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->status = $status;
        $this->description = $description;
        $this->hourlyRate = $hourlyRate;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters
    public function getId(): int {
        return $this->id;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function getHourlyRate(): float {
        return $this->hourlyRate;
    }

    public function getCreatedAt(): \DateTime {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime {
        return $this->updatedAt;
    }

    // Setters
    public function setType(string $type): void {
        $this->type = $type;
    }

    public function setStatus(string $status): void {
        $this->status = $status;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function setHourlyRate(float $hourlyRate): void {
        $this->hourlyRate = $hourlyRate;
    }

    // Business logic methods
    public function isAvailable(): bool {
        return $this->status === 'available';
    }

    public function isInMaintenance(): bool {
        return $this->status === 'maintenance';
    }

    public function isRented(): bool {
        return $this->status === 'rented';
    }

    // Convert to array for JSON response
    public function toArray(): array {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
            'hourlyRate' => $this->hourlyRate,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
} 