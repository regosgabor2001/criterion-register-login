<?php
namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use CriterionRegisterLogin\Service\AuthService;
use CriterionRegisterLogin\Model\User;

/**
 * Class AuthServiceTest
 * Handles integration testing for the authentication services, verifying domain logic
 * and database layer persistence interaction patterns.
 */
class AuthServiceTest extends TestCase {
    
    /**
     * Set up the test environment before each execution loop.
     * * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        // Typically used to trigger database transactions or seed pristine isolation structures
    }

    /**
     * Verify that the authentication gate strictly rejects authentication attempts
     * when an invalid password payload is provided.
     * * @test
     * @return void
     */
    public function test_user_cannot_login_with_incorrect_password(): void {
        // --- 1. Arrange ---
        // Seed a controlled state: hash a known password using production-mirror Argon2id specs
        $hashedPassword = password_hash('rightPassword', PASSWORD_ARGON2ID);
        
        $user = new User([
            'username' => 'tester',
            'email'    => 'test@gmail.com',
            'password' => $hashedPassword
        ]);
        // Persist the test target into the database lifecycle layer
        $user->save();

        $authService = new AuthService();

        // --- 2. Assert (Expectation) ---
        // Declare expected outcomes beforehand as standard protocol for intercepting exceptions in PHPUnit.
        // Asserts that user-enumeration data leakage risks remain safely plugged.
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Hibás email cím vagy jelszó.');

        // --- 3. Act ---
        // Execute the targeted method under bad credential parameters to trigger the exception block
        $authService->login('test@gmail.com', 'badPassword');
    }
}