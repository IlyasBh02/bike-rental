<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\User;
use PDO;

class CustomerRepository {
    private PDO $db;
    private UserRepository $userRepository;

    public function __construct(PDO $db, UserRepository $userRepository) {
        $this->db = $db;
        $this->userRepository = $userRepository;
    }

    public function findAll(): array {
        $stmt = $this->db->query('
            SELECT c.*, u.* 
            FROM customers c 
            JOIN users u ON c.user_id = u.id 
            ORDER BY c.created_at DESC
        ');
        $customers = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $customers[] = $this->createCustomerFromRow($row);
        }
        return $customers;
    }

    public function findById(int $id): ?Customer {
        $stmt = $this->db->prepare('
            SELECT c.*, u.* 
            FROM customers c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.id = :id
        ');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->createCustomerFromRow($row) : null;
    }

    public function findByUserId(int $userId): ?Customer {
        $stmt = $this->db->prepare('
            SELECT c.*, u.* 
            FROM customers c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.user_id = :user_id
        ');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->createCustomerFromRow($row) : null;
    }

    public function findByEmail(string $email): ?Customer {
        $stmt = $this->db->prepare('
            SELECT c.*, u.* 
            FROM customers c 
            JOIN users u ON c.user_id = u.id 
            WHERE u.email = :email
        ');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->createCustomerFromRow($row) : null;
    }

    public function findBySubscriptionType(string $subscriptionType): array {
        $stmt = $this->db->prepare('SELECT * FROM customers WHERE subscription_type = :subscription_type ORDER BY id');
        $stmt->execute(['subscription_type' => $subscriptionType]);
        $customers = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $customers[] = $this->createCustomerFromRow($row);
        }
        return $customers;
    }

    public function save(Customer $customer): Customer {
        // D'abord, sauvegarder l'utilisateur
        $user = new User(
            $customer->getId(),
            $customer->getEmail(),
            $customer->getPassword(),
            $customer->getFirstName(),
            $customer->getLastName(),
            'customer',
            $customer->getPhone(),
            $customer->getAddress()
        );
        $savedUser = $this->userRepository->save($user);

        if ($customer->getId() === 0) {
            return $this->insert($customer, $savedUser->getId());
        }
        return $this->update($customer);
    }

    private function insert(Customer $customer, int $userId): Customer {
        $stmt = $this->db->prepare('
            INSERT INTO customers (
                user_id,
                subscription_type,
                created_at
            ) VALUES (
                :user_id,
                :subscription_type,
                CURRENT_TIMESTAMP
            ) RETURNING id
        ');

        $stmt->execute([
            'user_id' => $userId,
            'subscription_type' => $customer->getSubscriptionType()
        ]);

        $customer->setId((int) $stmt->fetchColumn());
        return $customer;
    }

    private function update(Customer $customer): Customer {
        $stmt = $this->db->prepare('
            UPDATE customers
            SET subscription_type = :subscription_type,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ');

        $stmt->execute([
            'id' => $customer->getId(),
            'subscription_type' => $customer->getSubscriptionType()
        ]);

        return $customer;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM customers WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    private function createCustomerFromRow(array $row): Customer {
        $customer = new Customer(
            (int) $row['id'],
            $row['email'],
            $row['password'],
            $row['first_name'],
            $row['last_name'],
            $row['subscription_type'],
            $row['phone'],
            $row['address']
        );
        return $customer;
    }
} 