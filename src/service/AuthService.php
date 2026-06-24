<?php
namespace CriterionRegisterLogin\Service;

use CriterionRegisterLogin\Model\User;

class AuthService {
    public function register(array $data): bool {
        if (User::whereEmail($data['email'])) {
            throw new \Exception("Ez az email cím már foglalt.");
        }

        $user = new User([
            'username'   => $data['username'],
            'email'      => $data['email'],
            'password'   => password_hash($data['password'], PASSWORD_ARGON2ID, ['memory_cost' => 1<<17, 'time_cost' => 4, 'threads' => 2]),
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
        ]);

        $user->save();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_username'] = $user->username;

        return true;
    }

    public function login(string $emailUsername, string $password): bool {
        $user = User::whereEmail($emailUsername);

        if (!$user) {
            $user = User::whereUsername($emailUsername);
        }

        if (!$user || !password_verify($password, $user->password)) {
            throw new \Exception("Hibás email cím vagy jelszó.");
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_username'] = $user->username;

        return true;
    }

    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }

        session_destroy();
    }
}