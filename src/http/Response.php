<?php
namespace CriterionRegisterLogin\Http;

class Response {
    private $content;
    private int $status;
    private array $headers = [];

    public function __construct($content = '', int $status = 200, array $headers = []) {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    // JSON válasz generálása (Laravel: return response()->json([...]))
    public static function json(array $data, int $status = 200): self {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];
        return new self(json_encode($data), $status, $headers);
    }

    // Sima szöveges/HTML válasz
    public static function make($content = '', int $status = 200): self {
        return new self($content, $status);
    }

    // Átirányítás (Laravel: return redirect('/home'))
    public static function redirect(string $url, int $status = 302): self {
        $response = new self('', $status);
        $response->header('Location', $url);
        return $response;
    }

    // Header hozzáadása láncolhatóan
    public function header(string $key, string $value): self {
        $this->headers[$key] = $value;
        return $this;
    }

    // A válasz tényleges kiküldése a böngészőnek
    public function send(): void {
        // Státuszkód beállítása
        http_response_code($this->status);

        // Headerek kiküldése
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        // Tartalom kiírása
        echo $this->content;
    }
}