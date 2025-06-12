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
    <title>Gestion des Locations - Bike Rental</title>
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
            <h1>Gestion des Locations</h1>
            <?php if ($isAdmin): ?>
            <button class="btn btn-primary" onclick="showAddRentalModal()">
                <i class="bi bi-plus-circle"></i> Nouvelle location
            </button>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vélo</th>
                        <th>Client</th>
                        <th>Date de début</th>
                        <th>Date de fin</th>
                        <th>Montant total</th>
                        <th>Statut</th>
                        <?php if ($isAdmin): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="rentalsTableBody">
                    <!-- Les locations seront chargées ici dynamiquement -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal pour ajouter/modifier une location -->
    <?php if ($isAdmin): ?>
    <div class="modal fade" id="rentalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rentalModalTitle">Nouvelle location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="rentalForm">
                        <input type="hidden" id="rentalId">
                        <div class="mb-3">
                            <label for="bike_id" class="form-label">Vélo</label>
                            <select class="form-select" id="bike_id" name="bike_id" required>
                                <!-- Les vélos seront chargés ici dynamiquement -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Client</label>
                            <select class="form-select" id="customer_id" name="customer_id" required>
                                <!-- Les clients seront chargés ici dynamiquement -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Date de début</label>
                            <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">Date de fin</label>
                            <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="total_amount" class="form-label">Montant total (€)</label>
                            <input type="number" class="form-control" id="total_amount" name="total_amount" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active">Active</option>
                                <option value="completed">Terminée</option>
                                <option value="cancelled">Annulée</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="saveRental()">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonction pour charger les locations
        async function loadRentals() {
            try {
                const response = await fetch('/bike-rental/public/api/rentals');
                const rentals = await response.json();
                
                const tbody = document.getElementById('rentalsTableBody');
                tbody.innerHTML = '';
                
                rentals.forEach(rental => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${rental.id}</td>
                        <td>${rental.bike.type}</td>
                        <td>${rental.customer.firstName} ${rental.customer.lastName}</td>
                        <td>${new Date(rental.start_date).toLocaleString()}</td>
                        <td>${new Date(rental.end_date).toLocaleString()}</td>
                        <td>${rental.total_amount}€</td>
                        <td>${rental.status}</td>
                        <?php if ($isAdmin): ?>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editRental(${rental.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteRental(${rental.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                        <?php endif; ?>
                    `;
                    tbody.appendChild(tr);
                });
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors du chargement des locations');
            }
        }

        <?php if ($isAdmin): ?>
        // Fonction pour charger les vélos disponibles
        async function loadAvailableBikes() {
            try {
                const response = await fetch('/bike-rental/public/api/bikes');
                const bikes = await response.json();
                
                const select = document.getElementById('bike_id');
                select.innerHTML = '<option value="">Sélectionner un vélo</option>';
                
                bikes.forEach(bike => {
                    if (bike.status === 'available') {
                        const option = document.createElement('option');
                        option.value = bike.id;
                        option.textContent = `${bike.type} - ${bike.hourly_rate}€/h`;
                        select.appendChild(option);
                    }
                });
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors du chargement des vélos');
            }
        }

        // Fonction pour charger les clients
        async function loadCustomers() {
            try {
                const response = await fetch('/bike-rental/public/api/customers');
                const customers = await response.json();
                
                const select = document.getElementById('customer_id');
                select.innerHTML = '<option value="">Sélectionner un client</option>';
                
                customers.forEach(customer => {
                    const option = document.createElement('option');
                    option.value = customer.id;
                    option.textContent = `${customer.firstName} ${customer.lastName}`;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors du chargement des clients');
            }
        }

        // Fonction pour afficher le modal d'ajout
        function showAddRentalModal() {
            document.getElementById('rentalModalTitle').textContent = 'Nouvelle location';
            document.getElementById('rentalForm').reset();
            document.getElementById('rentalId').value = '';
            loadAvailableBikes();
            loadCustomers();
            new bootstrap.Modal(document.getElementById('rentalModal')).show();
        }

        // Fonction pour éditer une location
        async function editRental(id) {
            try {
                const response = await fetch(`/bike-rental/public/api/rentals/${id}`);
                const rental = await response.json();
                
                document.getElementById('rentalModalTitle').textContent = 'Modifier la location';
                document.getElementById('rentalId').value = rental.id;
                document.getElementById('bike_id').value = rental.bike_id;
                document.getElementById('customer_id').value = rental.customer_id;
                document.getElementById('start_date').value = rental.start_date.slice(0, 16);
                document.getElementById('end_date').value = rental.end_date.slice(0, 16);
                document.getElementById('total_amount').value = rental.total_amount;
                document.getElementById('status').value = rental.status;
                
                loadAvailableBikes();
                loadCustomers();
                new bootstrap.Modal(document.getElementById('rentalModal')).show();
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors du chargement de la location');
            }
        }

        // Fonction pour sauvegarder une location
        async function saveRental() {
            const id = document.getElementById('rentalId').value;
            const data = {
                bike_id: document.getElementById('bike_id').value,
                customer_id: document.getElementById('customer_id').value,
                start_date: document.getElementById('start_date').value,
                end_date: document.getElementById('end_date').value,
                total_amount: document.getElementById('total_amount').value,
                status: document.getElementById('status').value
            };

            try {
                const response = await fetch(`/bike-rental/public/api/rentals${id ? `/${id}` : ''}`, {
                    method: id ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('rentalModal')).hide();
                    loadRentals();
                } else {
                    const error = await response.json();
                    alert(error.error || 'Erreur lors de la sauvegarde');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la sauvegarde');
            }
        }

        // Fonction pour supprimer une location
        async function deleteRental(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette location ?')) {
                return;
            }

            try {
                const response = await fetch(`/bike-rental/public/api/rentals/${id}`, {
                    method: 'DELETE'
                });

                if (response.ok) {
                    loadRentals();
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

        // Charger les locations au chargement de la page
        document.addEventListener('DOMContentLoaded', loadRentals);
    </script>
</body>
</html> 