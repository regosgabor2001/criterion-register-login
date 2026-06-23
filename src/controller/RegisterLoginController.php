<?php
namespace CriterionRegisterLogin\Controller;

require_once __DIR__ . '/../../vendor/autoload.php';
use CriterionRegisterLogin\Utility\Twig;

class RegisterLoginController {
    public function registerPage() {
        $twig = new Twig();
        echo $twig->render('register-login.html.twig');
    }
}