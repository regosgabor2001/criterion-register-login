<?php

namespace CriterionRegisterLogin\Database;

/**
 * Class Database
 * Handles the application's relational database connectivity life cycle.
 */
class Database
{
    /**
     * Stores the active database connection resource to enforce a shared state across execution contexts.
     * @var \mysqli|null
     */
    private static $connection;

    /**
     * Database constructor.
     * Initializes the database connection by dynamically parsing environment configurations.
     */
    public function __construct()
    {
        $envPath = __DIR__ . '/../../.env';

        // Check for the existence of the environment file to prevent runtime errors in cloud/container setups.
        if (file_exists($envPath)) {
            $env = parse_ini_file($envPath);
            
            // Extract parameters or fallback to safe defaults to prevent variable initialization crashes
            $dbHost = $env['DB_HOST'] ?? '';
            $dbName = $env['DB_NAME'] ?? '';
            $dbUser = $env['DB_USER'] ?? '';
            $dbPass = $env['DB_PASS'] ?? '';
        } else {
            // Terminate execution safely if configuration values are completely missing
            die("Hiányzik a .env fájl.");
        }

        // Establish the low-level connection via MySQLi extension
        self::$connection = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName) or die('Nem sikerült csatlakozni az adatbázishoz.');
    }

    /**
     * Retrieves the instantiated, globally shared connection instance.
     * * @return \mysqli
     */
    public function getConnection()
    {
        return self::$connection;
    }
}