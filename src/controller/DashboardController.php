<?php
namespace CriterionRegisterLogin\Controller;

use CriterionRegisterLogin\Http\Auth;
use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;
use CriterionRegisterLogin\Utility\Twig;

class DashboardController {
    public function index(): Response {
        $user = Auth::user();

        $twig = new Twig();
        return Response::make($twig->render('dashboard.html.twig', [
            'user' => $user,
            'csrf_token' => Request::generateToken()
        ]));
    }
}