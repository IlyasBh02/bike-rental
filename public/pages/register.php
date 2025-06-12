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
    <title>Inscription - Bike Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Inscription</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($_GET['error']); ?>
                            </div>
                        <?php endif; ?>
                        <form id="registerForm" method="POST" action="/bike-rental/public/api/auth/register">
                            <div class="mb-3">
                                <label for="firstName" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                            </div>
                            <div class="mb-3">
                                <label for="lastName" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Adresse</label>
                                <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">S'inscrire</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                firstName: document.getElementById('firstName').value,
                lastName: document.getElementById('lastName').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                phone: document.getElementById('phone').value,
                address: document.getElementById('address').value
            };

            try {
                const response = await fetch('/bike-rental/public/api/auth/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (response.ok) {
                    window.location.href = '/bike-rental/public/pages/index.php';
                } else {
                    alert(data.error || 'Erreur lors de l\'inscription');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'inscription');
            }
        });
    </script>
</body>
</html> 