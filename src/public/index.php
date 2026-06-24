<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use CriterionRegisterLogin\Router\Router;
use CriterionRegisterLogin\Middleware\AuthMiddleware;
use CriterionRegisterLogin\Middleware\GuestMiddleware;
use CriterionRegisterLogin\Middleware\VerifyCsrfToken;
use CriterionRegisterLogin\Middleware\RateLimiter;
use CriterionRegisterLogin\Controller\RegisterLoginController;
use CriterionRegisterLogin\Controller\DashboardController;
use CriterionRegisterLogin\Controller\LogoutController;

Router::get('/', RegisterLoginController::class . '@registerLoginpage')->middleware(GuestMiddleware::class);
Router::post('/register', RegisterLoginController::class . '@register')->middleware([VerifyCsrfToken::class, RateLimiter::class]);
Router::post('/login', RegisterLoginController::class . '@login')->middleware([VerifyCsrfToken::class, RateLimiter::class]);
Router::post('/logout', LogoutController::class . '@logout')->middleware([AuthMiddleware::class, VerifyCsrfToken::class, RateLimiter::class]);
Router::get('/dashboard', DashboardController::class . '@index')->middleware(AuthMiddleware::class);
Router::dispatch();