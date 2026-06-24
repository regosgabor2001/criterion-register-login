<?php
namespace CriterionRegisterLogin\Http;

class Validator {
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules) {
        $this->data = $data;
        $this->rules = $rules;
    }

    public function validate(): bool {
        foreach ($this->rules as $field => $ruleset) {
            $rules = is_string($ruleset) ? explode('|', $ruleset) : $ruleset;
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $parameters = [];

                if (strpos($rule, ':') !== false) {
                    list($rule, $paramString) = explode(':', $rule, 2);
                    $parameters = explode(',', $paramString);
                }

                $methodName = 'validate' . ucfirst($rule);

                if (method_exists($this, $methodName)) {
                    if (!$this->$methodName($field, $value, $parameters)) {
                        break;
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function errors(): array {
        return $this->errors;
    }

    protected function validateRequired($field, $value): bool {
        if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
            $this->errors[$field][] = "A(z) {$field} mező kitöltése kötelező.";
            return false;
        }
        return true;
    }

    protected function validateEmail($field, $value): bool {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "A(z) {$field} mező formátuma érvénytelen.";
            return false;
        }
        return true;
    }

    protected function validateMin($field, $value, $parameters): bool {
        $min = (int)$parameters[0];
        if (!empty($value) && strlen((string)$value) < $min) {
            $this->errors[$field][] = "A(z) {$field} legalább {$min} karakter hosszú kell legyen.";
            return false;
        }
        return true;
    }

    protected function validateMax($field, $value, $parameters): bool {
        $max = (int)$parameters[0];
        if (!empty($value) && strlen((string)$value) > $max) {
            $this->errors[$field][] = "A(z) {$field} legfeljebb {$max} karakter hosszú lehet.";
            return false;
        }
        return true;
    }

    protected function validateConfirmed($field, $value): bool {
        $confirmationField = $field . '_confirmation';
        $confirmationValue = $this->data[$confirmationField] ?? null;

        if ($value !== $confirmationValue) {
            $this->errors[$field][] = "A(z) {$field} megerősítése nem egyezik.";
            return false;
        }
        return true;
    }
}