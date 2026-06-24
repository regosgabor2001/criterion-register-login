<?php
namespace CriterionRegisterLogin\Http;

use CriterionRegisterLogin\Http\Auth;
use CriterionRegisterLogin\Http\Validator;
use CriterionRegisterLogin\Model\User;

class Request {
    private array $data;
    private array $server;

    public function __construct() {
        $this->data = array_merge($_GET, $_POST, $this->getJsonInput());
        $this->server = $_SERVER;
    }

    private function getJsonInput(): array {
        $input = file_get_contents('php://input');
        $decoded = json_decode($input, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function capture(): self {
        return new self();
    }

    public function all(): array {
        return $this->data;
    }

    public function input(string $key, $default = null): mixed {
        return $this->data[$key] ?? $default;
    }

    public function only(array $keys): array {
        return array_intersect_key($this->data, array_flip($keys));
    }

    public function has(string $key): bool {
        return isset($this->data[$key]);
    }

    public function method(): string {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string {
        return parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }

    public function user(): ?User {
        return Auth::user();
    }

    public function validate(array $rules): array {
        $validator = new Validator($this->all(), $rules);

        if (!$validator->validate()) {
            if ($this->method() === 'POST' && (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
                $response = Response::json([
                    'message' => 'A megadott adatok érvénytelenek.',
                    'errors' => $validator->errors()
                ], 422);
                $response->send();
                exit;
            }

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['errors'] = $validator->errors();
            $_SESSION['old'] = $this->all();

            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            Response::redirect($referer)->send();
            exit;
        }

        return $this->only(array_keys($rules));
    }

    public static function generateToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
}