<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use CriterionRegisterLogin\Router\Router;
use CriterionRegisterLogin\Middleware\AuthMiddleware;
use CriterionRegisterLogin\Middleware\GuestMiddleware;
use CriterionRegisterLogin\Controller\RegisterLoginController;
use CriterionRegisterLogin\Controller\DashboardController;
use CriterionRegisterLogin\Controller\LogoutController;

Router::get('/', RegisterLoginController::class . '@registerLoginpage')->middleware(GuestMiddleware::class);
Router::post('/register', RegisterLoginController::class . '@register');
Router::post('/login', RegisterLoginController::class . '@login');
Router::get('/logout', LogoutController::class . '@logout')->middleware(AuthMiddleware::class);
Router::get('/dashboard', DashboardController::class . '@index')->middleware(AuthMiddleware::class);
Router::dispatch();