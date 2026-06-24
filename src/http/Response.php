<?php
namespace CriterionRegisterLogin\Http;

/**
 * Class Response
 * Models an HTTP response payload. It encapsulates headers, status codes, and 
 * the body content, ensuring output is sent explicitly rather than implicitly.
 */
class Response {
    private $content;
    private int $status;
    private array $headers = [];

    /**
     * Response constructor.
     * * @param mixed $content The raw string or encoded response body.
     * @param int $status Valid HTTP status code (defaults to 200 OK).
     * @param array $headers Accompanying HTTP headers.
     */
    public function __construct($content = '', int $status = 200, array $headers = []) {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    /**
     * Factory method to prepare a structured JSON response payload.
     * Automatically injects the correct application/json Content-Type headers.
     * * @param array $data
     * @param int $status
     * @return self
     */
    public static function json(array $data, int $status = 200): self {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];
        return new self(json_encode($data), $status, $headers);
    }

    /**
     * Factory method to generate a standard raw/HTML response object.
     * * @param mixed $content
     * @param int $status
     * @return self
     */
    public static function make($content = '', int $status = 200): self {
        return new self($content, $status);
    }

    /**
     * Factory method to initialize an HTTP redirection state.
     * Defaults to a 302 Found status code.
     * * @param string $url Target redirect destination.
     * @param int $status
     * @return self
     */
    public static function redirect(string $url, int $status = 302): self {
        $response = new self('', $status);
        $response->header('Location', $url);
        return $response;
    }

    /**
     * Fluent setter to append or override specific HTTP response headers.
     * Allows chainable method calls.
     * * @param string $key
     * @param string $value
     * @return self
     */
    public function header(string $key, string $value): self {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Dispatches the response context to the client browser.
     * Transmits status codes, iterates and registers headers, and echoes the body content.
     * * @return void
     */
    public function send(): void {
        // Apply the execution context's HTTP response code
        http_response_code($this->status);

        // Register all queued custom or system-wide headers
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        // Output the final stream body
        echo $this->content;
    }
}