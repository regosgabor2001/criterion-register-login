<?php

namespace CriterionRegisterLogin\Controller;

use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;
use CriterionRegisterLogin\Database\Database;
use CriterionRegisterLogin\Service\AuthService;

/**
 * Class LogoutController
 * Handles the termination of the user session.
 */
class LogoutController {
    
    /**
     * Log out the current user and redirect to the landing page.
     * * @param Request $request
     * @return Response
     */
    public function logout(Request $request) {
        // Delegate the session destruction and state cleanup to the dedicated AuthService
        $authService = new AuthService;
        $authService->logout();

        // Perform a clean HTTP redirection to the home/login route, 
        // preventing form resubmission and ensuring a stateless transition.
        return Response::redirect('/');
    }
}