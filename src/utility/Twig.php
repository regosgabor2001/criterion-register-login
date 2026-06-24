<?php

namespace CriterionRegisterLogin\Utility;

/**
 * Class Twig
 * Template engine abstraction layer encapsulating the Twig Environment.
 * Automatically injects and flushes session flash variables (errors, old inputs) to user view states.
 */
class Twig {
    private $twig;

    /**
     * Twig utility constructor.
     * Initializes filesystem loaders, environment configurations, and registers stateful global parameters.
     */
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Configure the filesystem path targeting the presentation layer view files
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../view');
        
        // Boot up the Twig environment with an explicit template compilation cache directory to boost performance
        $this->twig = new \Twig\Environment($loader, [
            'cache' => __DIR__ . '/../../var/cache/twig',
        ]);

        // Flash Data Management: Extract temporary validation anomalies and old input buffers from session storage
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? [];

        // Bind data pools globally so all rendered templates have immediate access to validation alerts and values
        $this->twig->addGlobal('errors', $errors);
        $this->twig->addGlobal('old', $old);

        // Volatile Flash Clean Up: Destruct the values inside session state instantly post-injection 
        // to prevent data leaking into subsequent unrelated requests
        unset($_SESSION['errors']);
        unset($_SESSION['old']);
    }

    /**
     * Compiles and evaluates a specified template file using custom array context arguments.
     * * @param string $template Path string relative to the configured view directory (e.g., 'dashboard.twig').
     * @param array $data Contextual dynamic variables to pass downstream to the compilation scope.
     * @return string Parsed semantic HTML output buffer.
     */
    public function render(string $template, array $data = []): string {
        return $this->twig->render($template, $data);
    }
}