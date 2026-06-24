<?php
namespace CriterionRegisterLogin\Http;

/**
 * Class Validator
 * Provides a robust, rule-based data validation engine using dynamic method dispatching
 * to parse and enforce compliance across varying input payloads.
 */
class Validator {
    private array $data;
    private array $rules;
    private array $errors = [];

    /**
     * Validator constructor.
     * * @param array $data Raw input array to undergo validation checks.
     * @param array $rules Mapping array linking fields to their string-based validation ruleset.
     */
    public function __construct(array $data, array $rules) {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * Executes the comprehensive validation suite over the injected dataset.
     * Parsed rules call their corresponding handler methods dynamically.
     * * @return bool Returns true if the dataset passes all rules without appending errors.
     */
    public function validate(): bool {
        foreach ($this->rules as $field => $ruleset) {
            // Support both piping format strings ("required|string|min:3") and raw array configurations
            $rules = is_string($ruleset) ? explode('|', $ruleset) : $ruleset;
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $parameters = [];

                // Extract rule-specific constraints or parameters defined after a colon (e.g., "min:3")
                if (strpos($rule, ':') !== false) {
                    list($rule, $paramString) = explode(':', $rule, 2);
                    $parameters = explode(',', $paramString);
                }

                // Standardize method naming conventions dynamically (e.g., "required" maps to "validateRequired")
                $methodName = 'validate' . ucfirst($rule);

                // Check method availability via safe lookups before execution
                if (method_exists($this, $methodName)) {
                    // Invoke the validation method using variable method names
                    if (!$this->$methodName($field, $value, $parameters)) {
                        // Fail-fast behavior per field: Stop assessing a single field if one rule breaks
                        break;
                    }
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Retrieves the comprehensive map of recorded validation violations.
     * * @return array Multi-dimensional associative array containing error messages grouped by field.
     */
    public function errors(): array {
        return $this->errors;
    }

    /**
     * Validates that a targeted input parameter is present and contains content.
     */
    protected function validateRequired($field, $value): bool {
        if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
            $this->errors[$field][] = "A(z) {$field} mező kitöltése kötelező.";
            return false;
        }
        return true;
    }

    /**
     * Validates that the field complies with valid, secure RFC-compliant email standards.
     */
    protected function validateEmail($field, $value): bool {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "A(z) {$field} mező formátuma érvénytelen.";
            return false;
        }
        return true;
    }

    /**
     * Enforces a minimum string length check using safe multi-byte safe lookups or type-casts.
     */
    protected function validateMin($field, $value, $parameters): bool {
        $min = (int)$parameters[0];
        if (!empty($value) && strlen((string)$value) < $min) {
            $this->errors[$field][] = "A(z) {$field} legalább {$min} karakter hosszú kell legyen.";
            return false;
        }
        return true;
    }

    /**
     * Enforces an upper-bound string length constraint over an input field.
     */
    protected function validateMax($field, $value, $parameters): bool {
        $max = (int)$parameters[0];
        if (!empty($value) && strlen((string)$value) > $max) {
            $this->errors[$field][] = "A(z) {$field} legfeljebb {$max} karakter hosszú lehet.";
            return false;
        }
        return true;
    }

    /**
     * Validates that an input field value mirrors its corresponding validation match 
     * (e.g., password matching with password_confirmation field).
     */
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