<?php
class Database {
    private static $host = 'localhost';
    private static $db = 'bike_rental';
    private static $user = 'root';
    private static $pass = '';
    private static $charset = 'utf8mb4';

    public static function connect() {
        $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=" . self::$charset;
        try {
            return new PDO($dsn, self::$user, self::$pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } catch (PDOException $e) {
            die("Erreur DB: " . $e->getMessage());
        }
    }
}
