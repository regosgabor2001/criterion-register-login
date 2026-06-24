<?php
namespace CriterionRegisterLogin\Http;

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
}