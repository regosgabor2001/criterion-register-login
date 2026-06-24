<?php
namespace CriterionRegisterLogin\Middleware;

require_once __DIR__ . '/../../vendor/autoload.php';
use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;

class AuthMiddleware {
    public function handle(Request $request): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                Response::json(['error' => 'Nincs bejelentkezve!'], 401)->send();
            } else {
                Response::redirect('/')->send();
            }
            return false;
        }

        return true;
    }
}