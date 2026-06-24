<?php

namespace CriterionRegisterLogin\Utility;

class Twig {
    private $twig;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../view');
        $this->twig = new \Twig\Environment($loader, [
            'cache' => false,
        ]);

        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? [];

        $this->twig->addGlobal('errors', $errors);
        $this->twig->addGlobal('old', $old);

        unset($_SESSION['errors']);
        unset($_SESSION['old']);
    }

    public function render(string $template, array $data = []): string {
        return $this->twig->render($template, $data);
    }
}