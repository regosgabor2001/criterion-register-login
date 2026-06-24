<?php

namespace CriterionRegisterLogin\Http;

use CriterionRegisterLogin\Model\User;

class Auth {
    private static ?User $currentUser = null;

    public static function user(): ?User {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (self::$currentUser !== null) {
            return self::$currentUser;
        }

        if (isset($_SESSION['user_id'])) {
            self::$currentUser = User::find((int)$_SESSION['user_id']);
            return self::$currentUser;
        }

        return null;
    }
}