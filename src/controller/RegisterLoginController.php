<?php
namespace CriterionRegisterLogin\Controller;

use CriterionRegisterLogin\Service\AuthService;
use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;
use CriterionRegisterLogin\Utility\Twig;

class RegisterLoginController {
    public function registerLoginpage(): Response {
        $twig = new Twig();
        return Response::make($twig->render('register-login.html.twig', [
            'csrf_token' => Request::generateToken()
        ]));
    }

    public function register(Request $request): Response {
        $validated = $request->validate([
            'username' => 'required|string|min:3|max:20',
            'email' => 'required|email',
            'first_name' => 'required|string|min:2|max:50',
            'last_name' => 'required|string|min:2|max:50',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|string|same:password'
        ]);

        $authService = new AuthService;

        try {
            $authService->register($validated);
            return Response::redirect('/dashboard');
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }
    }

    public function login(Request $request): Response {
        $validated = $request->validate([
            'email_username'    => 'required|string',
            'password' => 'required',
        ]);

        $authService = new AuthService;

        try {
            $authService->login($validated['email_username'], $validated['password']);
            return Response::redirect('/dashboard');
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 401);
        }
    }
}