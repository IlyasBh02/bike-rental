<?php

namespace App\Models;

class User {
    private $id;
    private $email;
    private $password;
    private $firstName;
    private $lastName;
    private $role;
    private $phone;
    private $address;
    private $createdAt;
    private $updatedAt;

    public function __construct(
        int $id = 0,
        string $email = '',
        string $password = '',
        string $firstName = '',
        string $lastName = '',
        string $role = 'customer',
        ?string $phone = null,
        ?string $address = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->role = $role;
        $this->phone = $phone;
        $this->address = $address;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters
    public function getId(): int {
        return $this->id;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getFirstName(): string {
        return $this->firstName;
    }

    public function getLastName(): string {
        return $this->lastName;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function getPhone(): ?string {
        return $this->phone;
    }

    public function getAddress(): ?string {
        return $this->address;
    }

    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string {
        return $this->updatedAt;
    }

    // Setters
    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setPassword(string $password): void {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function setFirstName(string $firstName): void {
        $this->firstName = $firstName;
    }

    public function setLastName(string $lastName): void {
        $this->lastName = $lastName;
    }

    public function setRole(string $role): void {
        $this->role = $role;
    }

    public function setPhone(?string $phone): void {
        $this->phone = $phone;
    }

    public function setAddress(?string $address): void {
        $this->address = $address;
    }

    // Business logic methods
    public function isAdmin(): bool {
        return $this->role === 'admin';
    }

    public function isCustomer(): bool {
        return $this->role === 'customer';
    }

    public function getFullName(): string {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->password);
    }

    // Convert to array for JSON response
    public function toArray(): array {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'role' => $this->role,
            'phone' => $this->phone,
            'address' => $this->address,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
} 