<?php

namespace CriterionRegisterLogin\Controller;

use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;
use CriterionRegisterLogin\Database\Database;
use CriterionRegisterLogin\Service\AuthService;

class LogoutController {
    private AuthService $authService;

    public function __construct() {
        $database = new Database();
        $this->authService = new AuthService($database->getConnection());
    }

    public function logout(Request $request) {
        $authService = new AuthService;
        $authService->logout();

        return Response::redirect('/');
    }
}