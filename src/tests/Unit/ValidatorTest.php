<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use CriterionRegisterLogin\Http\Validator;

/**
 * Class ValidatorTest
 * Enforces strict isolated unit tests over the custom Validator engine component,
 * ensuring independent rule assessment without touching database dependencies.
 */
class ValidatorTest extends TestCase {

    /**
     * Verify that the validator component returns a true validation state
     * and clears the error registry when all input datasets comply with rules.
     * * @test
     * @return void
     */
    public function test_it_passes_when_data_is_valid(): void {
        // --- Arrange ---
        $data = ['email' => 'test@gmail.hu', 'password' => 'Secret123'];
        $rules = ['email' => 'required', 'password' => 'required'];

        // --- Act ---
        $validator = new Validator($data, $rules);

        // --- Assert ---
        // Verify the system transitions cleanly into a successful verification state
        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());
    }

    /**
     * Verify that the validation engine flags failures and registers 
     * structural errors when a mandatory 'required' rule constraint is breached.
     * * @test
     * @return void
     */
    public function test_it_fails_when_required_field_is_missing(): void {
        // --- Arrange ---
        $data = ['email' => ''];
        $rules = ['email' => 'required'];

        // --- Act ---
        $validator = new Validator($data, $rules);

        // --- Assert ---
        // Verify the evaluation breaks as expected
        $this->assertFalse($validator->validate());
        // Confirm that the validation error array explicitly traces violations down to the failed field key
        $this->assertArrayHasKey('email', $validator->errors());
    }
}