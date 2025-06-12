<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;

// Charger la configuration
$config = Database::getConfig();

// Configurer la session
session_set_cookie_params(
    $config['session']['lifetime'],
    $config['session']['path'],
    $config['session']['domain'],
    $config['session']['secure'],
    $config['session']['httponly']
);

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurer le rapport d'erreurs
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Définir le fuseau horaire
date_default_timezone_set('Europe/Paris');

// Gérer les erreurs
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    error_log("Erreur [$errno] $errstr sur la ligne $errline dans le fichier $errfile");
    
    if (Database::getConfig()['app']['debug']) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    
    return true;
}); 