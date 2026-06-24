<?php

namespace CriterionRegisterLogin\Controller;

use CriterionRegisterLogin\Service\AuthService;
use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;
use CriterionRegisterLogin\Utility\Twig;

/**
 * Class RegisterLoginController
 * Manages user authentication workflows, including rendering forms,
 * handling registration, and processing login requests.
 */
class RegisterLoginController {
    
    /**
     * Renders the unified registration and login view.
     * * @return Response
     */
    public function registerLoginpage(): Response {
        $twig = new Twig();
        return Response::make($twig->render('register-login.html.twig', [
            // Ensure the initial view is protected against Cross-Site Request Forgery
            'csrf_token' => Request::generateToken()
        ]));
    }

    /**
     * Handles the HTTP POST request to register a new user.
     * * @param Request $request
     * @return Response
     */
    public function register(Request $request): Response {
        // Execute input validation rules natively parsed by the Request abstraction layer.
        // This acts as the first defensive wall against malformed data.
        $validated = $request->validate([
            'username'         => 'required|string|min:3|max:20',
            'email'            => 'required|email',
            'first_name'       => 'required|string|min:2|max:50',
            'last_name'        => 'required|string|min:2|max:50',
            'password'         => 'required|string|min:6',
            'confirm_password' => 'required|string|same:password'
        ]);

        $authService = new AuthService;

        try {
            // Delegate the user creation business logic and database layer storage to AuthService
            $authService->register($validated);
            return Response::redirect('/dashboard');
        } catch (\Exception $e) {
            // Gracefully handle business rule violations (e.g., duplicate email) 
            // by returning a structured JSON response with a 400 Bad Request status.
            return Response::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Handles the HTTP POST request to authenticate an existing user.
     * * @param Request $request
     * @return Response
     */
    public function login(Request $request): Response {
        // Enforce basic mandatory checks before passing raw inputs downstream
        $validated = $request->validate([
            'email_username' => 'required|string',
            'password'       => 'required',
        ]);

        $authService = new AuthService;

        try {
            // Attempt user authentication via AuthService
            $authService->login($validated['email_username'], $validated['password']);
            return Response::redirect('/dashboard');
        } catch (\Exception $e) {
            // Return an explicit 401 Unauthorized status on invalid credentials,
            // strictly following proper REST/HTTP semantic conventions.
            return Response::json(['error' => $e->getMessage()], 401);
        }
    }
}