<?php

namespace App\Repositories;

use App\Models\User;
use PDO;

class UserRepository {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findById(int $id): ?User {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? $this->createUserFromData($data) : null;
    }

    public function findByEmail(string $email): ?User {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? $this->createUserFromData($data) : null;
    }

    public function findAll(): array {
        $stmt = $this->db->query('SELECT * FROM users ORDER BY created_at DESC');
        $users = [];
        
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $this->createUserFromData($data);
        }
        
        return $users;
    }

    public function save(User $user): User {
        if ($user->getId()) {
            return $this->update($user);
        }
        return $this->insert($user);
    }

    private function insert(User $user): User {
        $sql = 'INSERT INTO users (email, password, first_name, last_name, role, phone, address) 
                VALUES (?, ?, ?, ?, ?, ?, ?) 
                RETURNING *';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $user->getEmail(),
            $user->getPassword(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getRole(),
            $user->getPhone(),
            $user->getAddress()
        ]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $this->createUserFromData($data);
    }

    private function update(User $user): User {
        $sql = 'UPDATE users 
                SET email = ?, password = ?, first_name = ?, last_name = ?, 
                    role = ?, phone = ?, address = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ? 
                RETURNING *';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $user->getEmail(),
            $user->getPassword(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getRole(),
            $user->getPhone(),
            $user->getAddress(),
            $user->getId()
        ]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $this->createUserFromData($data);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }

    private function createUserFromData(array $data): User {
        return new User(
            (int)$data['id'],
            $data['email'],
            $data['password'],
            $data['first_name'],
            $data['last_name'],
            $data['role'],
            $data['phone'],
            $data['address'],
            $data['created_at'],
            $data['updated_at']
        );
    }
} 