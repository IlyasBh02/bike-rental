<?php

namespace App;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;
    private static array $config;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                self::$config = require __DIR__ . '/config/config.php';
                $dbConfig = self::$config['db'];
                
                $dsn = sprintf(
                    "pgsql:host=%s;port=%s;dbname=%s",
                    $dbConfig['host'],
                    $dbConfig['port'],
                    $dbConfig['dbname']
                );

                if (self::$config['app']['debug']) {
                    error_log("Tentative de connexion à la base de données: " . $dsn);
                }

                self::$instance = new PDO(
                    $dsn,
                    $dbConfig['username'],
                    $dbConfig['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );

                if (self::$config['app']['debug']) {
                    error_log("Connexion à la base de données réussie");
                }
            } catch (PDOException $e) {
                error_log("Erreur de connexion à la base de données: " . $e->getMessage());
                throw new \Exception("Erreur de connexion à la base de données. Veuillez vérifier la configuration.");
            }
        }

        return self::$instance;
    }

    public static function getConfig(): array {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/config/config.php';
        }
        return self::$config;
    }
}
