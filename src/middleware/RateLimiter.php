<?php

namespace CriterionRegisterLogin\Middleware;

use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;

class RateLimiter {
    private int $maxAttempts = 5;
    private int $decayMinutes = 1;

    public function handle(Request $request): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $routeKey = md5($ip . '|' . $request->path());

        $currentTime = time();

        if (!isset($_SESSION['rate_limit'][$routeKey])) {
            $_SESSION['rate_limit'][$routeKey] = [
                'attempts' => 0,
                'reset_time' => $currentTime + ($this->decayMinutes * 60)
            ];
        }

        $limitData = &$_SESSION['rate_limit'][$routeKey];

        if ($currentTime > $limitData['reset_time']) {
            $limitData['attempts'] = 0;
            $limitData['reset_time'] = $currentTime + ($this->decayMinutes * 60);
        }

        if ($limitData['attempts'] >= $this->maxAttempts) {
            $remainingSeconds = $limitData['reset_time'] - $currentTime;
            
            Response::make("Túl sok kérés! Kérjük, próbálja újra " . $remainingSeconds . " másodperc múlva.", 429)->header("Retry-After: ", $remainingSeconds)->send();
            exit;
        }

        $limitData['attempts']++;
    }
}