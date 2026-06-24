<?php
namespace CriterionRegisterLogin\Middleware;

use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;

/**
 * Class AuthMiddleware
 * Acts as an HTTP pipeline interceptor (Guard) ensuring that the incoming request
 * originates from an authenticated session before allowing execution to proceed downstream.
 */
class AuthMiddleware {
    
    /**
     * Handles and evaluates the authentication state of the incoming request.
     * * @param Request $request
     * @return bool Returns true if the session is verified; otherwise, intercepts the cycle and returns false.
     */
    public function handle(Request $request): bool {
        // Lazily initialize the session state if not already started by previous execution layers.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // State Guard: Intercept the request if the mandatory session identifier is missing.
        if (!isset($_SESSION['user_id'])) {
            // Content-Negotiation: Determine if the unauthorized client expects a JSON error payload (AJAX/Fetch)...
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                Response::json(['error' => 'Nincs bejelentkezve!'], 401)->send();
            } else {
                // ...or trigger a clean standard HTTP redirection back to the root/login gate.
                Response::redirect('/')->send();
            }
            
            // Terminate further middleware chain or controller dispatching execution
            return false;
        }

        // Allow the request context to proceed to the next layer in the application pipeline
        return true;
    }
}