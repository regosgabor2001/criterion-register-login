<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use CriterionRegisterLogin\Router\Router;
use CriterionRegisterLogin\Controller\RegisterLoginController;

Router::get('/register', RegisterLoginController::class . '@registerPage')->name('register.show');
Router::dispatch();