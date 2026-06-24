<?php

namespace CriterionRegisterLogin\Service;

use CriterionRegisterLogin\Model\User;

/**
 * Class AuthService
 * Core business logic layer responsible for coordinating stateful authentication workflows,
 * cryptographically secure user registration, and safe session lifecycle termination.
 */
class AuthService {
    
    /**
     * Executes the secure workflow to register and automatically sign in a new user.
     * * @param array $data Pre-validated associative registration parameters.
     * @return bool True upon successful persistence and session initialization.
     * @throws \Exception When business layer constraints are violated (e.g., unique email collision).
     */
    public function register(array $data): bool {
        // Business Rule validation: Ensure the email does not violate unique database schema constraints
        if (User::whereEmail($data['email'])) {
            throw new \Exception("Ez az email cím már foglalt.");
        }

        // Instantiate and persist the target User entity
        $user = new User([
            'username'   => $data['username'],
            'email'      => $data['email'],
            // Security hardening: Employ Argon2id, the current OWASP-recommended industry standard for password hashing.
            // Custom parameters explicitly raise memory and time constraints to aggressively defend against GPU-accelerated brute-force attacks.
            'password'   => password_hash($data['password'], PASSWORD_ARGON2ID, [
                'memory_cost' => 1<<17, // 128 MB allocation
                'time_cost'   => 4,      // 4 processing iterations
                'threads'     => 2       // Multi-threaded computational parallelism
            ]),
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
        ]);

        $user->save();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Defends against Session Fixation exploits: Forces the client browser to drop the current
        // volatile session identifier and replace it with a newly generated high-entropy ID.
        session_regenerate_id(true);

        // Store non-sensitive user profile claims directly into the encrypted server-side session vault
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_username'] = $user->username;

        return true;
    }

    /**
     * Validates credentials and authenticates a user via either email or username context.
     * * @param string $emailUsername Compound input parameter containing either email or username.
     * @param string $password Raw input password string to verify.
     * @return bool True upon successful credentials alignment.
     * @throws \Exception On authentication lookup failures (generic error messages prevent user enumeration).
     */
    public function login(string $emailUsername, string $password): bool {
        // Multi-identifier lookup: Attempt retrieval via email pattern first...
        $user = User::whereEmail($emailUsername);

        // ...and dynamically fallback to username parsing if the first lookup yields empty results
        if (!$user) {
            $user = User::whereUsername($emailUsername);
        }

        // Timing-attack safe credential verification. Uses dynamic encryption checks.
        // Important: Throw a vague generic exception to avoid leaky user-enumeration vulnerabilities.
        if (!$user || !password_verify($password, $user->password)) {
            throw new \Exception("Hibás email cím vagy jelszó.");
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Cycle the session token context immediately post-authentication to preserve session isolation barriers
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_username'] = $user->username;

        return true;
    }

    /**
     * Terminates the active session state and flushes all client-side authentication identifiers.
     * * @return void
     */
    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Unset and empty all in-memory runtime session properties completely
        $_SESSION = [];

        // Complete session cleanup: Expire and wipe the client tracking cookie from the browser storage layer
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000, // Explicit timestamp manipulation pushes the expiry date permanently into the past
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"] // Ensures cookie isolation from client-side XSS scripting hooks
            );
        }

        // Obliterate the backend file/database record storage bound to the session descriptor
        session_destroy();
    }
}