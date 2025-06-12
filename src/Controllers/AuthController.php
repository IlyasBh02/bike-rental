<?php

namespace App\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;

class AuthController {
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function login() {
        try {
            $input = file_get_contents('php://input');
            error_log("Données reçues: " . $input);
            
            $data = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Erreur de décodage JSON: " . json_last_error_msg());
                http_response_code(400);
                return json_encode(['error' => 'Données invalides']);
            }

            if (!isset($data['email']) || !isset($data['password'])) {
                error_log("Email ou mot de passe manquant");
                http_response_code(400);
                return json_encode(['error' => 'Email et mot de passe requis']);
            }

            $user = $this->userRepository->findByEmail($data['email']);
            if (!$user) {
                error_log("Utilisateur non trouvé: " . $data['email']);
                http_response_code(401);
                return json_encode(['error' => 'Email ou mot de passe incorrect']);
            }

            if (!password_verify($data['password'], $user->getPassword())) {
                error_log("Mot de passe incorrect pour: " . $data['email']);
                http_response_code(401);
                return json_encode(['error' => 'Email ou mot de passe incorrect']);
            }

            // Démarrer la session si ce n'est pas déjà fait
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Stocker les informations de l'utilisateur en session
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_role'] = $user->getRole();

            error_log("Connexion réussie pour: " . $data['email']);
            return json_encode([
                'message' => 'Connexion réussie',
                'user' => $user->toArray()
            ]);
        } catch (\Exception $e) {
            error_log("Erreur de connexion: " . $e->getMessage());
            http_response_code(500);
            return json_encode(['error' => 'Erreur lors de la connexion']);
        }
    }

    public function register() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Vérifier les champs requis
            $requiredFields = ['email', 'password', 'firstName', 'lastName'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    http_response_code(400);
                    return json_encode(['error' => 'Tous les champs sont requis']);
                }
            }

            // Vérifier si l'email existe déjà
            if ($this->userRepository->findByEmail($data['email'])) {
                http_response_code(400);
                return json_encode(['error' => 'Cet email est déjà utilisé']);
            }

            // Créer le nouvel utilisateur
            $user = new User(
                0,
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['firstName'],
                $data['lastName'],
                'customer',
                $data['phone'] ?? null,
                $data['address'] ?? null
            );

            // Sauvegarder l'utilisateur
            $savedUser = $this->userRepository->save($user);

            // Démarrer la session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Stocker les informations de l'utilisateur en session
            $_SESSION['user_id'] = $savedUser->getId();
            $_SESSION['user_role'] = $savedUser->getRole();

            return json_encode([
                'message' => 'Inscription réussie',
                'user' => $savedUser->toArray()
            ]);
        } catch (\Exception $e) {
            error_log("Erreur d'inscription: " . $e->getMessage());
            http_response_code(500);
            return json_encode(['error' => 'Erreur lors de l\'inscription']);
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_destroy();
        return json_encode(['message' => 'Déconnexion réussie']);
    }

    public function getCurrentUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            return json_encode(['error' => 'Non authentifié']);
        }

        $user = $this->userRepository->findById($_SESSION['user_id']);
        if (!$user) {
            http_response_code(401);
            return json_encode(['error' => 'Utilisateur non trouvé']);
        }

        return json_encode($user->toArray());
    }

    public function requireAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non authentifié']);
            return false;
        }

        return true;
    }

    public function requireAdmin() {
        if (!$this->requireAuth()) {
            return false;
        }

        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Accès non autorisé']);
            return false;
        }

        return true;
    }
} 