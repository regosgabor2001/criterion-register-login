<?php
namespace CriterionRegisterLogin\Http;

use CriterionRegisterLogin\Http\Auth;
use CriterionRegisterLogin\Http\Validator;
use CriterionRegisterLogin\Model\User;

/**
 * Class Request
 * Represents an HTTP request, abstracting global superglobals ($_GET, $_POST, $_SERVER) 
 * and providing unified input handling, content-negotiated validation, and security utilities.
 */
class Request {
    private array $data;
    private array $server;

    /**
     * Request constructor.
     * Merges URL parameters, URL-encoded form data, and raw JSON payloads into a single input source.
     */
    public function __construct() {
        $this->data = array_merge($_GET, $_POST, $this->getJsonInput());
        $this->server = $_SERVER;
    }

    /**
     * Reads and parses raw JSON payloads from the HTTP request body (`php://input`).
     * * @return array
     */
    private function getJsonInput(): array {
        $input = file_get_contents('php://input');
        $decoded = json_decode($input, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Static factory method to capture the current HTTP execution context.
     * * @return self
     */
    public static function capture(): self {
        return new self();
    }

    /**
     * Returns all processed input parameters.
     * * @return array
     */
    public function all(): array {
        return $this->data;
    }

    /**
     * Safely retrieves a specific input value with an optional fallback.
     * * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function input(string $key, $default = null): mixed {
        return $this->data[$key] ?? $default;
    }

    /**
     * Returns a filtered subset of inputs containing only the specified keys.
     * * @param array $keys
     * @return array
     */
    public function only(array $keys): array {
        return array_intersect_key($this->data, array_flip($keys));
    }

    /**
     * Determines whether a given input key is present in the request.
     * * @param string $key
     * @return bool
     */
    public function has(string $key): bool {
        return isset($this->data[$key]);
    }

    /**
     * Returns the normalized HTTP request method (e.g., 'GET', 'POST').
     * * @return string
     */
    public function method(): string {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Extracts and cleans the sanitized request path component from the URI.
     * * @return string
     */
    public function path(): string {
        return parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }

    /**
     * Accesses the currently logged-in user instance associated with this request.
     * * @return User|null
     */
    public function user(): ?User {
        return Auth::user();
    }

    /**
     * Validates request inputs against a set of predefined business validation rules.
     * Implements explicit content-negotiation to respond with JSON for APIs, 
     * or session flash variables with HTTP redirects for traditional HTML forms.
     * 
     * @param array $rules
     * @return array Sanitized subset containing validated parameters only.
     */
    public function validate(array $rules): array {
        $validator = new Validator($this->all(), $rules);

        if (!$validator->validate()) {
            // Content negotiation: Check if the client expects a structured JSON response (AJAX/Fetch)
            if ($this->method() === 'POST' && (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
                $response = Response::json([
                    'message' => 'A megadott adatok érvénytelenek.',
                    'errors' => $validator->errors()
                ], 422); // 422 Unprocessable Entity is the semantic standard for validation failures
                $response->send();
                exit;
            }

            // Fallback for traditional stateless browser forms: Flash data into the session store
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['errors'] = $validator->errors();
            $_SESSION['old'] = $this->all(); // Preserve user input to pre-populate form elements upon redirect

            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            Response::redirect($referer)->send();
            exit;
        }

        // Return only the verified attributes bound to the validation criteria to prevent mass-assignment vulnerabilities.
        return $this->only(array_keys($rules));
    }

    /**
     * Generates a cryptographically secure, random anti-CSRF token per user session context.
     * * @return string
     */
    public static function generateToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            // Generate a 256-bit cryptographically high-entropy token to prevent cross-site replay attacks
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
}