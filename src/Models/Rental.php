<?php

namespace App\Models;

class Rental {
    private int $id;
    private int $bikeId;
    private int $customerId;
    private \DateTime $startTime;
    private ?\DateTime $plannedEndTime;
    private ?\DateTime $actualEndTime;
    private float $totalAmount;
    private float $penaltyAmount;
    private string $status; // 'active', 'completed', 'cancelled'
    private ?string $notes;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        int $id = 0,
        int $bikeId = 0,
        int $customerId = 0,
        \DateTime $startTime = null,
        ?\DateTime $plannedEndTime = null,
        ?\DateTime $actualEndTime = null,
        float $totalAmount = 0.0,
        float $penaltyAmount = 0.0,
        string $status = 'active',
        ?string $notes = null
    ) {
        $this->id = $id;
        $this->bikeId = $bikeId;
        $this->customerId = $customerId;
        $this->startTime = $startTime ?? new \DateTime();
        $this->plannedEndTime = $plannedEndTime;
        $this->actualEndTime = $actualEndTime;
        $this->totalAmount = $totalAmount;
        $this->penaltyAmount = $penaltyAmount;
        $this->status = $status;
        $this->notes = $notes;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters
    public function getId(): int {
        return $this->id;
    }

    public function getBikeId(): int {
        return $this->bikeId;
    }

    public function getCustomerId(): int {
        return $this->customerId;
    }

    public function getStartTime(): \DateTime {
        return $this->startTime;
    }

    public function getPlannedEndTime(): ?\DateTime {
        return $this->plannedEndTime;
    }

    public function getActualEndTime(): ?\DateTime {
        return $this->actualEndTime;
    }

    public function getTotalAmount(): float {
        return $this->totalAmount;
    }

    public function getPenaltyAmount(): float {
        return $this->penaltyAmount;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getNotes(): ?string {
        return $this->notes;
    }

    public function getCreatedAt(): \DateTime {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime {
        return $this->updatedAt;
    }

    // Setters
    public function setBikeId(int $bikeId): void {
        $this->bikeId = $bikeId;
    }

    public function setCustomerId(int $customerId): void {
        $this->customerId = $customerId;
    }

    public function setStartTime(\DateTime $startTime): void {
        $this->startTime = $startTime;
    }

    public function setPlannedEndTime(?\DateTime $plannedEndTime): void {
        $this->plannedEndTime = $plannedEndTime;
    }

    public function setActualEndTime(?\DateTime $actualEndTime): void {
        $this->actualEndTime = $actualEndTime;
    }

    public function setTotalAmount(float $totalAmount): void {
        $this->totalAmount = $totalAmount;
    }

    public function setPenaltyAmount(float $penaltyAmount): void {
        $this->penaltyAmount = $penaltyAmount;
    }

    public function setStatus(string $status): void {
        $this->status = $status;
    }

    public function setNotes(?string $notes): void {
        $this->notes = $notes;
    }

    // Business logic methods
    public function isActive(): bool {
        return $this->status === 'active';
    }

    public function isCompleted(): bool {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool {
        return $this->status === 'cancelled';
    }

    public function getDuration(): ?\DateInterval {
        if (!$this->actualEndTime) {
            return null;
        }
        return $this->startTime->diff($this->actualEndTime);
    }

    public function getTotalDurationInHours(): ?float {
        $duration = $this->getDuration();
        if (!$duration) {
            return null;
        }
        return $duration->h + ($duration->days * 24) + ($duration->i / 60);
    }

    public function getFinalAmount(): float {
        return $this->totalAmount + $this->penaltyAmount;
    }

    // Convert to array for JSON response
    public function toArray(): array {
        return [
            'id' => $this->id,
            'bikeId' => $this->bikeId,
            'customerId' => $this->customerId,
            'startTime' => $this->startTime->format('Y-m-d H:i:s'),
            'plannedEndTime' => $this->plannedEndTime ? $this->plannedEndTime->format('Y-m-d H:i:s') : null,
            'actualEndTime' => $this->actualEndTime ? $this->actualEndTime->format('Y-m-d H:i:s') : null,
            'totalAmount' => $this->totalAmount,
            'penaltyAmount' => $this->penaltyAmount,
            'status' => $this->status,
            'notes' => $this->notes,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
} 