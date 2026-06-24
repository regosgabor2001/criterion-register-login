<?php

namespace CriterionRegisterLogin\Database;

use mysqli;

/**
 * Class Database
 * Handles the application's relational database connectivity life cycle.
 * Implements a thread-safe Singleton fallback pattern compliant with isolated test environments.
 */
class Database
{
    /**
     * Stores the active database connection resource to enforce a shared state across execution contexts.
     * @var mysqli|null
     */
    private static ?mysqli $connection = null;

    /**
     * Database constructor.
     * Initializes the database connection by checking system environment variables before falling back to local files.
     */
    public function __construct()
    {
        // 1. Precedence Check: Inspect superglobals for runtime variables injected via PHPUnit or Docker infrastructure
        $dbHost = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? null;
        $dbName = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? null;
        $dbUser = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? null;
        $dbPass = $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? null;

        // 2. Fallback Layer: If system environment values are absent, parse the physical local deployment configuration file
        if ($dbHost === null || $dbName === null) {
            $envPath = __DIR__ . '/../../.env';

            if (file_exists($envPath)) {
                $env = parse_ini_file($envPath);
                
                $dbHost = $env['DB_HOST'] ?? '';
                $dbName = $env['DB_NAME'] ?? '';
                $dbUser = $env['DB_USER'] ?? '';
                $dbPass = $env['DB_PASS'] ?? '';
            } else {
                // Halt runtime execution instantly if configurations are totally missing from the environment architecture
                die("Hiányzik a .env fájl vagy a környezeti változók konfigurációja.");
            }
        }

        // 3. Singleton Resource Gate: Only instantiate a network connection socket if it hasn't been mapped yet
        if (self::$connection === null) {
            self::$connection = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
            
            // Validate the physical socket connection state to catch misconfigurations instantly
            if (!self::$connection) {
                die('Nem sikerült csatlakozni az adatbázishoz: ' . mysqli_connect_error());
            }
            
            // Standardize charset encoding behavior across both development and testing connections
            mysqli_set_charset(self::$connection, 'utf8mb4');
        }
    }

    /**
     * Retrieves the instantiated, globally shared connection instance.
     * @return mysqli
     */
    public function getConnection(): mysqli
    {
        return self::$connection;
    }
}