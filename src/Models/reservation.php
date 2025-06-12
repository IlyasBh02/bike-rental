<?php

namespace App\Models;

class Reservation {
    private int $id;
    private int $customerId;
    private int $bikeId;
    private \DateTime $startTime;
    private \DateTime $endTime;
    private string $status; // 'pending', 'confirmed', 'cancelled', 'expired'
    private ?string $notes;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;
    private ?\DateTime $confirmedAt;

    public function __construct(
        int $id = 0,
        int $customerId = 0,
        int $bikeId = 0,
        ?\DateTime $startTime = null,
        ?\DateTime $endTime = null,
        string $status = 'pending',
        ?string $notes = null
    ) {
        $this->id = $id;
        $this->customerId = $customerId;
        $this->bikeId = $bikeId;
        $this->startTime = $startTime ?? new \DateTime();
        $this->endTime = $endTime ?? (clone $this->startTime)->modify('+1 hour');
        $this->status = $status;
        $this->notes = $notes;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->confirmedAt = null;
    }

    // Getters
    public function getId(): int {
        return $this->id;
    }

    public function getCustomerId(): int {
        return $this->customerId;
    }

    public function getBikeId(): int {
        return $this->bikeId;
    }

    public function getStartTime(): \DateTime {
        return $this->startTime;
    }

    public function getEndTime(): \DateTime {
        return $this->endTime;
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

    public function getConfirmedAt(): ?\DateTime {
        return $this->confirmedAt;
    }

    // Setters
    public function setCustomerId(int $customerId): void {
        $this->customerId = $customerId;
    }

    public function setBikeId(int $bikeId): void {
        $this->bikeId = $bikeId;
    }

    public function setStartTime(\DateTime $startTime): void {
        $this->startTime = $startTime;
    }

    public function setEndTime(\DateTime $endTime): void {
        $this->endTime = $endTime;
    }

    public function setStatus(string $status): void {
        $this->status = $status;
    }

    public function setNotes(?string $notes): void {
        $this->notes = $notes;
    }

    // Business logic methods
    public function isPending(): bool {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool {
        return $this->status === 'confirmed';
    }

    public function isCancelled(): bool {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool {
        return $this->status === 'expired';
    }

    public function confirm(): void {
        $this->status = 'confirmed';
        $this->confirmedAt = new \DateTime();
    }

    public function cancel(): void {
        $this->status = 'cancelled';
    }

    public function expire(): void {
        $this->status = 'expired';
    }

    public function getDuration(): \DateInterval {
        return $this->startTime->diff($this->endTime);
    }

    public function getDurationInHours(): float {
        $duration = $this->getDuration();
        return $duration->h + ($duration->days * 24) + ($duration->i / 60);
    }

    public function isExpiredByTime(): bool {
        $now = new \DateTime();
        return $now > $this->startTime;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'bike_id' => $this->bikeId,
            'customer_id' => $this->customerId,
            'start_time' => $this->startTime->format('Y-m-d H:i:s'),
            'end_time' => $this->endTime->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}
