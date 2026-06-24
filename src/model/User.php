<?php
namespace CriterionRegisterLogin\Model;

use CriterionRegisterLogin\Database\Database;
use mysqli;

/**
 * Class User
 * Implements the Active Record design pattern to bridge the gap between
 * the application's business logic layer and the relational 'users' database table.
 */
class User {
    // Public attributes mirroring database columns for explicit data visibility
    public ?int $id = null;
    public string $username;
    public string $email;
    public ?string $password = null;
    public string $first_name;
    public string $last_name;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    private mysqli $db;

    /**
     * User constructor.
     * Maps an optional associative array of database row metrics to class properties (Hydration).
     * * @param array $attributes
     */
    public function __construct(array $attributes = []) {
        $database = new Database();
        $this->db = $database->getConnection();

        $this->id = $attributes['id'] ?? null;
        $this->username = $attributes['username'] ?? '';
        $this->email = $attributes['email'] ?? '';
        $this->password = $attributes['password'] ?? null;
        $this->first_name = $attributes['first_name'] ?? '';
        $this->last_name = $attributes['last_name'] ?? '';
        $this->created_at = $attributes['created_at'] ?? null;
        $this->updated_at = $attributes['updated_at'] ?? null;
    }

    /**
     * Persists the active entity state back to the database.
     * Dispatches an UPDATE query if the record already exists, or an INSERT query if it is a new record.
     * Uses prepared statements exclusively to prevent SQL Injection vulnerabilities.
     * * @return bool True on successful execution context.
     */
    public function save(): bool {
        // Branch 1: Update existing record context based on primary key availability
        if ($this->id !== null) {
            $stmt = $this->db->prepare(
                "UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ? WHERE id = ?"
            );
            if (!$stmt) return false;

            // Heavily typed binding parameters prevent malicious type juggling and arbitrary query injections
            $stmt->bind_param(
                "ssssi", 
                $this->username, 
                $this->email, 
                $this->first_name, 
                $this->last_name, 
                $this->id
            );
            
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        // Branch 2: Create a completely new entry inside database state
        $stmt = $this->db->prepare(
            "INSERT INTO users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)"
        );
        if (!$stmt) return false;

        $stmt->bind_param(
            "sssss", 
            $this->username, 
            $this->email, 
            $this->password, 
            $this->first_name, 
            $this->last_name
        );
            
        $result = $stmt->execute();
        if ($result) {
            // Re-hydrate the object primary key property with the auto-generated database sequence index
            $this->id = $this->db->insert_id;
        }
        $stmt->close();
        return $result;
    }

    /**
     * Finder method: Query and hydrate a single User instance by its unique integer identifier.
     * * @param int $id
     * @return self|null
     */
    public static function find(int $id): ?self {
        $database = new Database();
        $db = $database->getConnection();

        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        if (!$stmt) return null;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        // Hydrate a fresh model object if data exists, otherwise return null semantic values safely
        return $row ? new self($row) : null;
    }

    /**
     * Finder method: Query and hydrate a single User instance by its unique email constraint.
     * * @param string $email
     * @return self|null
     */
    public static function whereEmail(string $email): ?self {
        $database = new Database();
        $db = $database->getConnection();

        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        if (!$stmt) return null;

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ? new self($row) : null;
    }

    /**
     * Finder method: Query and hydrate a single User instance by its unique alphanumeric username string.
     * * @param string $username
     * @return self|null
     */
    public static function whereUsername(string $username): ?self {
        $database = new Database();
        $db = $database->getConnection();

        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        if (!$stmt) return null;

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ? new self($row) : null;
    }
}