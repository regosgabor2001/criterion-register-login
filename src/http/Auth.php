<?php

namespace CriterionRegisterLogin\Http;

use CriterionRegisterLogin\Model\User;

/**
 * Class Auth
 * Provides a global authentication state manager and session accessor.
 */
class Auth {
    /**
     * In-memory cache for the authenticated User instance to avoid redundant database queries.
     * @var User|null
     */
    private static ?User $currentUser = null;

    /**
     * Retrieves the currently authenticated user context based on active session states.
     * * @return User|null
     */
    public static function user(): ?User {
        // Lazily initialize the PHP session if it has not been started yet.
        // This prevents headers-already-sent warnings while ensuring session access.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Memoization: Return the cached user instance if it was already fetched during this request lifecycle.
        if (self::$currentUser !== null) {
            return self::$currentUser;
        }

        // If a valid session identifier exists, hydrate the User model from the database persistence layer.
        if (isset($_SESSION['user_id'])) {
            self::$currentUser = User::find((int)$_SESSION['user_id']);
            return self::$currentUser;
        }

        // Return null if the visitor is an unauthenticated guest.
        return null;
    }
}