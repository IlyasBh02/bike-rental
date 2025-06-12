<?php

namespace App\Models;

class Customer extends User {
    private string $subscriptionType; // 'occasional', 'monthly', 'premium'
    private ?string $address;

    public function __construct(
        int $id = 0,
        string $email = '',
        string $password = '',
        string $firstName = '',
        string $lastName = '',
        string $subscriptionType = 'occasional',
        ?string $phone = null,
        ?string $address = null
    ) {
        parent::__construct(
            $id,
            $email,
            $password,
            $firstName,
            $lastName,
            'customer',
            $phone,
            $address
        );
        $this->subscriptionType = $subscriptionType;
        $this->address = $address;
    }

    // Getters
    public function getSubscriptionType(): string {
        return $this->subscriptionType;
    }

    public function getAddress(): ?string {
        return $this->address;
    }

    // Setters
    public function setSubscriptionType(string $subscriptionType): void {
        $this->subscriptionType = $subscriptionType;
    }

    public function setAddress(?string $address): void {
        $this->address = $address;
    }

    // Business logic methods
    public function hasPremiumSubscription(): bool {
        return $this->subscriptionType === 'premium';
    }

    public function hasMonthlySubscription(): bool {
        return $this->subscriptionType === 'monthly';
    }

    // Convert to array for JSON response
    public function toArray(): array {
        $userData = parent::toArray();
        return array_merge($userData, [
            'subscriptionType' => $this->subscriptionType
        ]);
    }
} 