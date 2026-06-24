<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use CriterionRegisterLogin\Http\Validator;

class ValidatorTest extends TestCase {

    public function test_it_passes_when_data_is_valid(): void {
        $data = ['email' => 'test@gmail.hu', 'password' => 'Secret123'];
        $rules = ['email' => 'required', 'password' => 'required'];

        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());
    }

    public function test_it_fails_when_required_field_is_missing(): void {
        $data = ['email' => ''];
        $rules = ['email' => 'required'];

        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $this->assertArrayHasKey('email', $validator->errors());
    }
}