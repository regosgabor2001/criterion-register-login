<?php
namespace CriterionRegisterLogin\Middleware;

use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;

/**
 * Class GuestMiddleware
 * Implements an inverse authentication guard (commonly known as "RedirectIfAuthenticated").
 * Prevents logged-in users from accessing purely guest-oriented pages like registration or login forms.
 */
class GuestMiddleware {
    
    /**
     * Handles and intercepts request workflows for authenticated users attempting to access guest routes.
     * * @param Request $request
     * @return bool Returns true if the user is a guest; otherwise, redirects to dashboard and returns false.
     */
    public function handle(Request $request): bool {
        // Ensure the session state layer is booted up before inspecting identifiers.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // If an active user session identifier exists, intercept the request lifecycle.
        if (isset($_SESSION['user_id'])) {
            // Forcefully route the authenticated user to the protected dashboard zone,
            // preventing redundant login actions or session collisions.
            Response::redirect('/dashboard')->send();
            
            // Halt the propagation down the remaining middleware pipeline or router dispatcher.
            return false; 
        }

        // Proceed normally if the visitor is an unauthenticated guest user.
        return true; 
    }
}