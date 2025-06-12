<?php

require_once __DIR__ . '/../src/bootstrap.php';

use App\Database;
use App\Controllers\BikeController;
use App\Controllers\CustomerController;
use App\Controllers\RentalController;
use App\Controllers\ReservationController;
use App\Controllers\AuthController;
use App\Repositories\BikeRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\RentalRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\UserRepository;

try {
    // Initialiser la base de données
    $db = Database::getInstance();

    // Créer les repositories
    $userRepository = new UserRepository($db);
    $bikeRepository = new BikeRepository($db);
    $customerRepository = new CustomerRepository($db, $userRepository);
    $rentalRepository = new RentalRepository($db);
    $reservationRepository = new ReservationRepository($db);

    // Créer les controllers
    $authController = new AuthController($userRepository);
    $bikeController = new BikeController($bikeRepository);
    $customerController = new CustomerController($customerRepository, $userRepository);
    $rentalController = new RentalController($rentalRepository, $bikeRepository, $customerRepository);
    $reservationController = new ReservationController($reservationRepository, $bikeRepository, $customerRepository);

    // Obtenir l'URL et la méthode
    $url = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];

    // Gérer les routes API
    if (strpos($url, '/bike-rental/public/api/') === 0) {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        // Routes d'authentification
        if ($url === '/bike-rental/public/api/auth/login' && $method === 'POST') {
            echo $authController->login();
        }
        elseif ($url === '/bike-rental/public/api/auth/register' && $method === 'POST') {
            echo $authController->register();
        }
        elseif ($url === '/bike-rental/public/api/auth/logout' && $method === 'POST') {
            echo $authController->logout();
        }
        elseif ($url === '/bike-rental/public/api/auth/current-user' && $method === 'GET') {
            echo $authController->getCurrentUser();
        }
        // Routes protégées
        else {
            // Vérifier l'authentification
            if (!$authController->requireAuth()) {
                exit();
            }

            // Routes admin
            if (strpos($url, '/bike-rental/public/api/admin/') === 0) {
                if (!$authController->requireAdmin()) {
                    exit();
                }
            }

            // Routes des vélos
            if ($url === '/bike-rental/public/api/bikes' || $url === '/bike-rental/public/api/bikes/') {
                if ($method === 'GET') {
                    echo $bikeController->index();
                } elseif ($method === 'POST') {
                    echo $bikeController->store();
                }
            }
            // Route d'un vélo spécifique
            elseif (preg_match('#^/bike-rental/public/api/bikes/(\d+)$#', $url, $matches)) {
                $id = (int)$matches[1];
                if ($method === 'GET') {
                    echo $bikeController->show($id);
                } elseif ($method === 'PUT') {
                    echo $bikeController->update($id);
                } elseif ($method === 'DELETE') {
                    echo $bikeController->destroy($id);
                }
            }
            // Autres routes...
            else {
                http_response_code(404);
                echo json_encode(['error' => 'Route non trouvée']);
            }
        }
    }
    // Routes frontend
    else {
        $path = parse_url($url, PHP_URL_PATH);
        
        // Rediriger vers la page de connexion si non authentifié
        if (!isset($_SESSION['user_id']) && 
            $path !== '/bike-rental/public/pages/login.php' && 
            $path !== '/bike-rental/public/pages/register.php') {
            header('Location: /bike-rental/public/pages/login.php');
            exit();
        }

        // Rediriger vers le dashboard si admin
        if (isset($_SESSION['user_role']) && 
            $_SESSION['user_role'] === 'admin' && 
            strpos($path, '/bike-rental/public/pages/admin/') === 0) {
            header('Location: /bike-rental/public/pages/admin/dashboard.php');
            exit();
        }

        $file = __DIR__ . $path;

        if (is_file($file)) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext === 'php') {
                require_once $file;
            } else {
                header('Content-Type: ' . mime_content_type($file));
                readfile($file);
            }
        } else {
            header('Content-Type: text/html');
            require_once __DIR__ . '/pages/index.php';
        }
    }
} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
    if (Database::getConfig()['app']['debug']) {
        throw $e;
    }
    http_response_code(500);
    echo json_encode(['error' => 'Une erreur est survenue']);
}
