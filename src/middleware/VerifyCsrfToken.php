<?php

namespace CriterionRegisterLogin\Middleware;

use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;

class VerifyCsrfToken {
    public function handle(Request $request): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $submittedToken = $_POST['csrf_token'] ?? '';
            $sessionToken = $_SESSION['csrf_token'] ?? '';

            if (empty($submittedToken) || !hash_equals($sessionToken, $submittedToken)) {
                Response::make("CSRF token hiányzik vagy érvénytelen.", 419)->send();
                exit;
            }
        }
    }
}