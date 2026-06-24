<?php
namespace CriterionRegisterLogin\Utility;

class Twig
{
    private static $twig;

    public function __construct()
    {
        if (self::$twig === null) {
            $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../view');
            self::$twig = new \Twig\Environment($loader);
        }
    }

    public function render($template, $data = [])
    {
        return self::$twig->render($template, $data);
    }
}