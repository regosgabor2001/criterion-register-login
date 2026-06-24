<?php

namespace CriterionRegisterLogin\Controller;

use CriterionRegisterLogin\Http\Auth;
use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;
use CriterionRegisterLogin\Utility\Twig;

/**
 * Class DashboardController
 * Handles authenticated user landpages and main entry points.
 */
class DashboardController {
    
    /**
     * Renders the primary dashboard view for authenticated users.
     * * @return Response
     */
    public function index(): Response {
        // Retrieve the currently authenticated user context from the session abstraction
        $user = Auth::user();

        $twig = new Twig();
        
        // Return an encapsulated HTTP Response object rather than directly echoing content.
        // This ensures compatibility with the middleware pipeline architecture.
        return Response::make($twig->render('dashboard.html.twig', [
            'user' => $user,
            // Generate a fresh CSRF token explicitly for frontend forms or AJAX interactions
            'csrf_token' => Request::generateToken()
        ]));
    }
}