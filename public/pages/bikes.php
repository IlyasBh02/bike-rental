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

// Vérifier l'authentification
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Vérifier si l'utilisateur est admin
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Vélos - Bike Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Bike Rental</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="bikes.php">Vélos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="rentals.php">Locations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reservations.php">Réservations</a>
                    </li>
                    <?php if ($isAdmin): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/dashboard.php">Admin</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex">
                    <button class="btn btn-light" onclick="logout()">Déconnexion</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gestion des Vélos</h1>
            <?php if ($isAdmin): ?>
            <button class="btn btn-primary" onclick="showAddBikeModal()">
                <i class="bi bi-plus-circle"></i> Ajouter un vélo
            </button>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Tarif horaire</th>
                        <th>Description</th>
                        <?php if ($isAdmin): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="bikesTableBody">
                    <!-- Les vélos seront chargés ici dynamiquement -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal pour ajouter/modifier un vélo -->
    <?php if ($isAdmin): ?>
    <div class="modal fade" id="bikeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bikeModalTitle">Ajouter un vélo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="bikeForm">
                        <input type="hidden" id="bikeId">
                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="city">Ville</option>
                                <option value="mountain">VTT</option>
                                <option value="electric">Électrique</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="available">Disponible</option>
                                <option value="rented">En location</option>
                                <option value="maintenance">En maintenance</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="hourly_rate" class="form-label">Tarif horaire (€)</label>
                            <input type="number" class="form-control" id="hourly_rate" name="hourly_rate" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="saveBike()">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonction pour charger les vélos
        async function loadBikes() {
            try {
                const response = await fetch('/bike-rental/public/api/bikes');
                const bikes = await response.json();
                
                const tbody = document.getElementById('bikesTableBody');
                tbody.innerHTML = '';
                
                bikes.forEach(bike => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${bike.id}</td>
                        <td>${bike.type}</td>
                        <td>${bike.status}</td>
                        <td>${bike.hourly_rate}€</td>
                        <td>${bike.description || ''}</td>
                        <?php if ($isAdmin): ?>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editBike(${bike.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteBike(${bike.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                        <?php endif; ?>
                    `;
                    tbody.appendChild(tr);
                });
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors du chargement des vélos');
            }
        }

        <?php if ($isAdmin): ?>
        // Fonction pour afficher le modal d'ajout
        function showAddBikeModal() {
            document.getElementById('bikeModalTitle').textContent = 'Ajouter un vélo';
            document.getElementById('bikeForm').reset();
            document.getElementById('bikeId').value = '';
            new bootstrap.Modal(document.getElementById('bikeModal')).show();
        }

        // Fonction pour éditer un vélo
        async function editBike(id) {
            try {
                const response = await fetch(`/bike-rental/public/api/bikes/${id}`);
                const bike = await response.json();
                
                document.getElementById('bikeModalTitle').textContent = 'Modifier le vélo';
                document.getElementById('bikeId').value = bike.id;
                document.getElementById('type').value = bike.type;
                document.getElementById('status').value = bike.status;
                document.getElementById('hourly_rate').value = bike.hourly_rate;
                document.getElementById('description').value = bike.description || '';
                
                new bootstrap.Modal(document.getElementById('bikeModal')).show();
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors du chargement du vélo');
            }
        }

        // Fonction pour sauvegarder un vélo
        async function saveBike() {
            const id = document.getElementById('bikeId').value;
            const data = {
                type: document.getElementById('type').value,
                status: document.getElementById('status').value,
                hourly_rate: document.getElementById('hourly_rate').value,
                description: document.getElementById('description').value
            };

            try {
                const response = await fetch(`/bike-rental/public/api/bikes${id ? `/${id}` : ''}`, {
                    method: id ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('bikeModal')).hide();
                    loadBikes();
                } else {
                    const error = await response.json();
                    alert(error.error || 'Erreur lors de la sauvegarde');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la sauvegarde');
            }
        }

        // Fonction pour supprimer un vélo
        async function deleteBike(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce vélo ?')) {
                return;
            }

            try {
                const response = await fetch(`/bike-rental/public/api/bikes/${id}`, {
                    method: 'DELETE'
                });

                if (response.ok) {
                    loadBikes();
                } else {
                    const error = await response.json();
                    alert(error.error || 'Erreur lors de la suppression');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la suppression');
            }
        }
        <?php endif; ?>

        // Fonction de déconnexion
        async function logout() {
            try {
                const response = await fetch('/bike-rental/public/api/auth/logout', {
                    method: 'POST'
                });
                
                if (response.ok) {
                    window.location.href = 'login.php';
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la déconnexion');
            }
        }

        // Charger les vélos au chargement de la page
        document.addEventListener('DOMContentLoaded', loadBikes);
    </script>
</body>
</html> 