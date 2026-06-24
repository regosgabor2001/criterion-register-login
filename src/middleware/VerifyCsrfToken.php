<?php

namespace CriterionRegisterLogin\Middleware;

use CriterionRegisterLogin\Http\Request;

class VerifyCsrfToken {
    public function handle(Request $request): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $submittedToken = $_POST['csrf_token'] ?? '';
            $sessionToken = $_SESSION['csrf_token'] ?? '';

            if (empty($submittedToken) || !hash_equals($sessionToken, $submittedToken)) {
                http_response_code(419);
                echo "CSRF token hiányzik vagy érvénytelen.";
                exit;
            }
        }
    }
}