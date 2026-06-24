<?php
namespace CriterionRegisterLogin\Database;

class Database
{
    private static $connection;

    public function __construct()
    {
        $envPath = __DIR__ . '/../../.env';

        if (file_exists($envPath)) {
            $env = parse_ini_file($envPath);
            
            $dbHost = $env['DB_HOST'] ?? '';
            $dbName = $env['DB_NAME'] ?? '';
            $dbUser = $env['DB_USER'] ?? '';
            $dbPass = $env['DB_PASS'] ?? '';
        } else {
            die("Hiányzik a .env fájl.");
        }

        self::$connection = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName) or die('Nem sikerült csatlakozni az adatbázishoz.');
    }

    public function getConnection()
    {
        return self::$connection;
    }
}