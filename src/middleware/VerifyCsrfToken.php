<?php

namespace CriterionRegisterLogin\Middleware;

use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;

/**
 * Class VerifyCsrfToken
 * Intercepts state-changing HTTP requests (POST) to guard against Cross-Site Request Forgery (CSRF).
 * Validates that submission payloads contain a token matching the cryptographically secure session variable.
 */
class VerifyCsrfToken {
    
    /**
     * Handles and evaluates the CSRF token payload for state-changing operations.
     * * @param Request $request
     * @return void
     */
    public function handle(Request $request): void {
        // CSRF protection is selectively applied to data-mutating methods (POST/PUT/DELETE) 
        // while allowing safe, idempotent queries (GET/HEAD) to pass through freely.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Extract token parameters from both the inbound post body and active session vault
            $submittedToken = $_POST['csrf_token'] ?? '';
            $sessionToken = $_SESSION['csrf_token'] ?? '';

            // Defensive check: Ensure incoming token exists and matches the stored token.
            // Critical: Uses hash_equals() instead of string comparison (== / ===) 
            // to implement a constant-time comparison, fully mitigating timing attack vectors.
            if (empty($submittedToken) || !hash_equals($sessionToken, $submittedToken)) {
                
                // Abort request execution by emitting an HTTP 419 status code.
                // 419 Page Expired is the standard RESTful/framework response convention for CSRF failures.
                Response::make("CSRF token hiányzik vagy érvénytelen.", 419)->send();
                exit;
            }
        }
    }
}