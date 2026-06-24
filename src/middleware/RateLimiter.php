<?php

namespace CriterionRegisterLogin\Middleware;

use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;

/**
 * Class RateLimiter
 * Implements a Request Throttling mechanism (Fixed/Sliding Window) to mitigate 
 * Brute-Force authentication attempts and Denial of Service (DoS) attacks on sensitive endpoints.
 */
class RateLimiter {
    // Maximum allowable request cycles before a client is temporarily throttled
    private int $maxAttempts = 5;
    // The cooldown period (lockout duration) measured in minutes
    private int $decayMinutes = 1;

    /**
     * Intercepts the request context to evaluate and update the client's execution limits.
     * * @param Request $request
     * @return void
     */
    public function handle(Request $request): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Track users via IP addresses while appending the request path to prevent cross-route throttling.
        // Hashing the compound string keeps the session storage key clean and consistent.
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $routeKey = md5($ip . '|' . $request->path());

        $currentTime = time();

        // Initialize the rate-limiting record bucket if this is the client's first interaction with the route.
        if (!isset($_SESSION['rate_limit'][$routeKey])) {
            $_SESSION['rate_limit'][$routeKey] = [
                'attempts' => 0,
                'reset_time' => $currentTime + ($this->decayMinutes * 60)
            ];
        }

        // Use a PHP reference operator (&) to mutate the targeted session array node in-place efficiently.
        $limitData = &$_SESSION['rate_limit'][$routeKey];

        // Decay Window check: Reset the counter if the restriction timeframe has naturally elapsed.
        if ($currentTime > $limitData['reset_time']) {
            $limitData['attempts'] = 0;
            $limitData['reset_time'] = $currentTime + ($this->decayMinutes * 60);
        }

        // Guard Condition: Intercept the execution if the client has breached maximum request limits.
        if ($limitData['attempts'] >= $this->maxAttempts) {
            $remainingSeconds = $limitData['reset_time'] - $currentTime;
            
            // Abort the lifecycle by emitting a semantic HTTP 429 status code.
            // Injects the standard RFC-compliant 'Retry-After' header to inform clients when to retry.
            Response::make("Túl sok kérés! Kérjük, próbálja újra " . $remainingSeconds . " másodperc múlva.", 429)
                ->header("Retry-After", (string)$remainingSeconds)
                ->send();
            exit;
        }

        // Increment the tracking parameter for non-violating operations
        $limitData['attempts']++;
    }
}