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

    public static function check(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_id'] ?? null;
    }
}