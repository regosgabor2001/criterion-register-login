<?php
namespace CriterionRegisterLogin\Model;

require_once __DIR__ . '/../../vendor/autoload.php';
use CriterionRegisterLogin\Database\Database;
use mysqli;

class User {
    public ?int $id = null;
    public string $username;
    public string $email;
    public ?string $password = null;
    public string $first_name;
    public string $last_name;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    private mysqli $db;

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

    public function save(): bool {
        if ($this->id !== null) {
            $stmt = $this->db->prepare(
                "UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ? WHERE id = ?"
            );
            if (!$stmt) return false;

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
            $this->id = $this->db->insert_id;
        }
        $stmt->close();
        return $result;
    }

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

        return $row ? new self($row) : null;
    }

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