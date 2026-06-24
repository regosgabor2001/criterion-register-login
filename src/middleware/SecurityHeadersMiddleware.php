<?php
namespace CriterionRegisterLogin\Middleware;

use CriterionRegisterLogin\Http\Request;

/**
 * Class SecurityHeadersMiddleware
 * Injects defensive HTTP response headers to harden the application layer,
 * mitigating web-based vectors such as XSS, Clickjacking, MIME-sniffing, and data leakage.
 */
class SecurityHeadersMiddleware {
    
    /**
     * Handles and appends strict security headers to the execution scope context.
     * * @param Request $request
     * @return void
     */
    public function handle(Request $request): void {
        // Content Security Policy (CSP): Mitigates Cross-Site Scripting (XSS) and data injection attacks.
        // Restricts asset loading (scripts, fonts, images) strictly to the origin server ('self').
        // Allows inline styles temporarily via 'unsafe-inline' for dynamic UI rendering components.
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';");

        // HTTP Strict Transport Security (HSTS): Forces browsers to connect exclusively via secure HTTPS
        // for the next calendar year (31,536,000 seconds), including all nested subdomains.
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");

        // X-Frame-Options: Defends against Clickjacking attacks by instructing the browser
        // to never render this application's pages inside an <iframe> or <frame> element.
        header("X-Frame-Options: DENY");

        // X-Content-Type-Options: Disables MIME-type sniffing vulnerabilities.
        // Forces the browser to strictly follow the Content-Type header declared by the server (e.g., executing scripts only if typed as text/javascript).
        header("X-Content-Type-Options: nosniff");

        // Referrer-Policy: Governs how much referral tracking metadata is sent when navigating away.
        // Sends the full URL when moving within the origin, but drops paths/queries during cross-origin shifts to prevent token leakage.
        header("Referrer-Policy: strict-origin-when-cross-origin");

        // Permissions-Policy: Explicitly disables high-risk browser hardware APIs (Camera, Mic, GPS)
        // to restrict surface area for drive-by exploits or privacy breaches.
        header("Permissions-Policy: geolocation=(), camera=(), microphone=()");
    }
}