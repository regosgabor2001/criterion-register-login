<?php
namespace CriterionRegisterLogin\Middleware;

use CriterionRegisterLogin\Http\Request;

class SecurityHeadersMiddleware {
    
    public function handle(Request $request): void {
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';");

        header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");

        header("X-Frame-Options: DENY");

        header("X-Content-Type-Options: nosniff");

        header("Referrer-Policy: strict-origin-when-cross-origin");

        header("Permissions-Policy: geolocation=(), camera=(), microphone=()");
    }
}