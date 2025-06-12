<?php
session_start();

// Fonction pour vérifier si l'utilisateur est authentifié
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier si l'utilisateur est admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Fonction pour rediriger vers la page de login si non authentifié
function requireAuth() {
    if (!isAuthenticated()) {
        if (isApiRequest()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Non authentifié']);
            exit();
        } else {
            header('Location: /bike-rental/public/pages/login.html');
            exit();
        }
    }
}

// Fonction pour rediriger vers la page d'accueil si non admin
function requireAdmin() {
    if (!isAdmin()) {
        if (isApiRequest()) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['error' => 'Accès non autorisé']);
            exit();
        } else {
            header('Location: /bike-rental/public/pages/index.html');
            exit();
        }
    }
}

// Fonction pour vérifier si la requête est une requête API
function isApiRequest() {
    return strpos($_SERVER['REQUEST_URI'], '/bike-rental/public/api/') === 0;
}

// Si l'utilisateur n'est pas connecté et n'est pas sur la page de login/register
if (!isset($_SESSION['user_id']) && 
    !strpos($_SERVER['REQUEST_URI'], 'login.html') && 
    !strpos($_SERVER['REQUEST_URI'], 'register.html')) {
    header('Location: /bike-rental/public/pages/login.html');
    exit();
}

// Si l'utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    // Si c'est un admin et qu'il n'est pas sur le dashboard
    if ($_SESSION['user_role'] === 'admin' && 
        !strpos($_SERVER['REQUEST_URI'], 'admin/dashboard.html')) {
        header('Location: /bike-rental/public/pages/admin/dashboard.html');
        exit();
    }
    
    // Si c'est un client et qu'il est sur le dashboard
    if ($_SESSION['user_role'] === 'customer' && 
        strpos($_SERVER['REQUEST_URI'], 'admin/dashboard.html')) {
        header('Location: /bike-rental/public/index.html');
        exit();
    }
} 