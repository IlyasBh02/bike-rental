<?php

return [
    'db' => [
        'host' => 'localhost',
        'port' => '5432',
        'dbname' => 'bike_rental',
        'username' => 'postgres',
        'password' => '12345678'
    ],
    'app' => [
        'url' => 'http://localhost/bike-rental',
        'debug' => true
    ],
    'session' => [
        'lifetime' => 7200, // 2 heures
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true
    ]
]; 