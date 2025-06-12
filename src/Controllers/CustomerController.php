<?php

namespace App\Controllers;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use App\Repositories\UserRepository;

class CustomerController {
    private CustomerRepository $customerRepository;
    private UserRepository $userRepository;

    public function __construct(
        CustomerRepository $customerRepository,
        UserRepository $userRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->userRepository = $userRepository;
    }

    public function index() {
        $customers = $this->customerRepository->findAll();
        return json_encode(array_map(fn($customer) => $customer->toArray(), $customers));
    }

    public function show(int $id) {
        $customer = $this->customerRepository->findById($id);
        if (!$customer) {
            http_response_code(404);
            return json_encode(['error' => 'Customer not found']);
        }
        return json_encode($customer->toArray());
    }

    public function store() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Vérifier si l'email existe déjà
        if ($this->userRepository->findByEmail($data['email'])) {
            http_response_code(400);
            return json_encode(['error' => 'Email already exists']);
        }

        $customer = new Customer(
            0,
            $data['email'],
            $data['password'],
            $data['firstName'],
            $data['lastName'],
            $data['subscriptionType'] ?? 'occasional',
            $data['phone'] ?? null,
            $data['address'] ?? null
        );

        $savedCustomer = $this->customerRepository->save($customer);
        http_response_code(201);
        return json_encode($savedCustomer->toArray());
    }

    public function update(int $id) {
        $customer = $this->customerRepository->findById($id);
        if (!$customer) {
            http_response_code(404);
            return json_encode(['error' => 'Customer not found']);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['email'])) $customer->setEmail($data['email']);
        if (isset($data['password'])) $customer->setPassword($data['password']);
        if (isset($data['firstName'])) $customer->setFirstName($data['firstName']);
        if (isset($data['lastName'])) $customer->setLastName($data['lastName']);
        if (isset($data['subscriptionType'])) $customer->setSubscriptionType($data['subscriptionType']);
        if (isset($data['phone'])) $customer->setPhone($data['phone']);
        if (isset($data['address'])) $customer->setAddress($data['address']);

        $updatedCustomer = $this->customerRepository->save($customer);
        return json_encode($updatedCustomer->toArray());
    }

    public function destroy(int $id) {
        $customer = $this->customerRepository->findById($id);
        if (!$customer) {
            http_response_code(404);
            return json_encode(['error' => 'Customer not found']);
        }

        $this->customerRepository->delete($id);
        http_response_code(204);
        return '';
    }
} 