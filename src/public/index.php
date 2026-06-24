<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use CriterionRegisterLogin\Router\Router;
use CriterionRegisterLogin\Middleware\AuthMiddleware;
use CriterionRegisterLogin\Middleware\GuestMiddleware;
use CriterionRegisterLogin\Middleware\VerifyCsrfToken;
use CriterionRegisterLogin\Middleware\RateLimiter;
use CriterionRegisterLogin\Middleware\SecurityHeadersMiddleware;
use CriterionRegisterLogin\Controller\RegisterLoginController;
use CriterionRegisterLogin\Controller\DashboardController;
use CriterionRegisterLogin\Controller\LogoutController;

/**
 * --------------------------------------------------------------------------
 * HTTP Routing and Middleware Pipeline Layer
 * --------------------------------------------------------------------------
 * Maps explicit URIs and HTTP methods to their corresponding controller actions.
 * Enforces a layered "Onion Architecture" pipeline where inbound requests pass 
 * through ordered authentication, security, and throttling middleware barriers.
 */

// Route: Root/Guest Entry Point
// Secures the browser session and ensures authenticated users are bypassed straight to the dashboard.
Router::get('/', RegisterLoginController::class . '@registerLoginpage')
    ->middleware([SecurityHeadersMiddleware::class, GuestMiddleware::class]);

// Route: User Account Creation
// Protected against XSS/Clickjacking (Security), state manipulation (CSRF), and automated brute-force scripts (RateLimiter).
Router::post('/register', RegisterLoginController::class . '@register')
    ->middleware([SecurityHeadersMiddleware::class, VerifyCsrfToken::class, RateLimiter::class]);

// Route: Authentication Gate
// Throttles login attempts to 5 requests per minute using the custom sliding window cache state.
Router::post('/login', RegisterLoginController::class . '@login')
    ->middleware([SecurityHeadersMiddleware::class, VerifyCsrfToken::class, RateLimiter::class]);

// Route: Session Termination
// Requires a valid, active user identity session along with anti-CSRF token verification before logging out.
Router::post('/logout', LogoutController::class . '@logout')
    ->middleware([SecurityHeadersMiddleware::class, AuthMiddleware::class, VerifyCsrfToken::class, RateLimiter::class]);

// Route: Protected Dashboard
// Main secure landing page. Defends corporate assets by rejecting unauthenticated guest traffic.
Router::get('/dashboard', DashboardController::class . '@index')
    ->middleware([SecurityHeadersMiddleware::class, AuthMiddleware::class]);

/**
 * --------------------------------------------------------------------------
 * Application Dispatch Engine
 * --------------------------------------------------------------------------
 * Processes the matched global Request context, iterates through the declared 
 * middleware stacks sequentially, and triggers the resolved Controller execution loop.
 */
Router::dispatch();