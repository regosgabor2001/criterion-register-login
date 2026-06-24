<?php
namespace CriterionRegisterLogin\Middleware;

use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;

class GuestMiddleware {
    public function handle(Request $request): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            Response::redirect('/dashboard')->send();
            return false; 
        }

        return true; 
    }
}