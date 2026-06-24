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

Router::get('/', RegisterLoginController::class . '@registerLoginpage')->middleware([SecurityHeadersMiddleware::class, GuestMiddleware::class]);
Router::post('/register', RegisterLoginController::class . '@register')->middleware([SecurityHeadersMiddleware::class, VerifyCsrfToken::class, RateLimiter::class]);
Router::post('/login', RegisterLoginController::class . '@login')->middleware([SecurityHeadersMiddleware::class, VerifyCsrfToken::class, RateLimiter::class]);
Router::post('/logout', LogoutController::class . '@logout')->middleware([SecurityHeadersMiddleware::class, AuthMiddleware::class, VerifyCsrfToken::class, RateLimiter::class]);
Router::get('/dashboard', DashboardController::class . '@index')->middleware([SecurityHeadersMiddleware::class, AuthMiddleware::class]);
Router::dispatch();