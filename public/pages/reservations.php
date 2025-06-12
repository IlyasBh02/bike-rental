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
    <title>Gestion des Réservations - Bike Rental</title>
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
            <h1>Gestion des Réservations</h1>
            <button class="btn btn-primary" onclick="showAddReservationModal()">
                <i class="bi bi-plus-circle"></i> Nouvelle réservation
            </button>
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
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="reservationsTableBody">
                    <!-- Les réservations seront chargées ici dynamiquement -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal pour ajouter/modifier une réservation -->
    <div class="modal fade" id="reservationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reservationModalTitle">Nouvelle réservation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="reservationForm">
                        <input type="hidden" id="reservationId">
                        <div class="mb-3">
                            <label for="bike_id" class="form-label">Vélo</label>
                            <select class="form-select" id="bike_id" name="bike_id" required>
                                <!-- Les vélos seront chargés ici dynamiquement -->
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
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending">En attente</option>
                                <option value="confirmed">Confirmée</option>
                                <option value="cancelled">Annulée</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="saveReservation()">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonction pour charger les réservations
        async function loadReservations() {
            try {
                const response = await fetch('/bike-rental/public/api/reservations');
                const reservations = await response.json();
                
                const tbody = document.getElementById('reservationsTableBody');
                tbody.innerHTML = '';
                
                reservations.forEach(reservation => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${reservation.id}</td>
                        <td>${reservation.bike.type}</td>
                        <td>${reservation.customer.firstName} ${reservation.customer.lastName}</td>
                        <td>${new Date(reservation.start_date).toLocaleString()}</td>
                        <td>${new Date(reservation.end_date).toLocaleString()}</td>
                        <td>${reservation.status}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editReservation(${reservation.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteReservation(${reservation.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors du chargement des réservations');
            }
        }

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

        // Fonction pour afficher le modal d'ajout
        function showAddReservationModal() {
            document.getElementById('reservationModalTitle').textContent = 'Nouvelle réservation';
            document.getElementById('reservationForm').reset();
            document.getElementById('reservationId').value = '';
            loadAvailableBikes();
            new bootstrap.Modal(document.getElementById('reservationModal')).show();
        }

        // Fonction pour éditer une réservation
        async function editReservation(id) {
            try {
                const response = await fetch(`/bike-rental/public/api/reservations/${id}`);
                const reservation = await response.json();
                
                document.getElementById('reservationModalTitle').textContent = 'Modifier la réservation';
                document.getElementById('reservationId').value = reservation.id;
                document.getElementById('bike_id').value = reservation.bike_id;
                document.getElementById('start_date').value = reservation.start_date.slice(0, 16);
                document.getElementById('end_date').value = reservation.end_date.slice(0, 16);
                document.getElementById('status').value = reservation.status;
                
                loadAvailableBikes();
                new bootstrap.Modal(document.getElementById('reservationModal')).show();
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors du chargement de la réservation');
            }
        }

        // Fonction pour sauvegarder une réservation
        async function saveReservation() {
            const id = document.getElementById('reservationId').value;
            const data = {
                bike_id: document.getElementById('bike_id').value,
                start_date: document.getElementById('start_date').value,
                end_date: document.getElementById('end_date').value,
                status: document.getElementById('status').value
            };

            try {
                const response = await fetch(`/bike-rental/public/api/reservations${id ? `/${id}` : ''}`, {
                    method: id ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('reservationModal')).hide();
                    loadReservations();
                } else {
                    const error = await response.json();
                    alert(error.error || 'Erreur lors de la sauvegarde');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la sauvegarde');
            }
        }

        // Fonction pour supprimer une réservation
        async function deleteReservation(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')) {
                return;
            }

            try {
                const response = await fetch(`/bike-rental/public/api/reservations/${id}`, {
                    method: 'DELETE'
                });

                if (response.ok) {
                    loadReservations();
                } else {
                    const error = await response.json();
                    alert(error.error || 'Erreur lors de la suppression');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la suppression');
            }
        }

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

        // Charger les réservations au chargement de la page
        document.addEventListener('DOMContentLoaded', loadReservations);
    </script>
</body>
</html> 