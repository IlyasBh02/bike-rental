<?php
require_once __DIR__ . '/../../src/Controllers/AuthController.php';
require_once __DIR__ . '/../../src/Repositories/UserRepository.php';
require_once __DIR__ . '/../../src/Models/User.php';
require_once __DIR__ . '/../../src/Database.php';

use App\Controllers\AuthController;
use App\Repositories\UserRepository;
use App\Database;

// Initialiser les dépendances
$db = Database::getInstance();
$userRepository = new UserRepository($db);
$authController = new AuthController($userRepository);

// Vérifier si l'utilisateur est déjà connecté
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Bike Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Connexion</h3>
                    </div>
                    <div class="card-body">
                        <div id="errorMessage" class="alert alert-danger d-none"></div>
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Se connecter</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('errorMessage');

            try {
                console.log('Tentative de connexion...');
                const response = await fetch('/bike-rental/public/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });

                console.log('Réponse reçue:', response.status);
                const data = await response.json();
                console.log('Données reçues:', data);

                if (response.ok) {
                    console.log('Connexion réussie, redirection...');
                    // Redirection basée sur le rôle
                    if (data.user.role === 'admin') {
                        window.location.href = '/bike-rental/public/pages/admin/dashboard.php';
                    } else {
                        window.location.href = '/bike-rental/public/pages/index.php';
                    }
                } else {
                    console.error('Erreur de connexion:', data.error);
                    errorMessage.textContent = data.error || 'Erreur de connexion';
                    errorMessage.classList.remove('d-none');
                }
            } catch (error) {
                console.error('Erreur:', error);
                errorMessage.textContent = 'Erreur de connexion au serveur';
                errorMessage.classList.remove('d-none');
            }
        });
    </script>
</body>
</html> 