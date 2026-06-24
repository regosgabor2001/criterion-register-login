<?php
namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use CriterionRegisterLogin\Service\AuthService;
use CriterionRegisterLogin\Model\User;

class AuthServiceTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
    }

    public function test_user_cannot_login_with_incorrect_password(): void {
        $hashedPassword = password_hash('rightPassword', PASSWORD_ARGON2ID);
        
        $user = new User([
            'username' => 'tester',
            'email' => 'test@gmail.com',
            'password' => $hashedPassword
        ]);
        $user->save();

        $authService = new AuthService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Hibás email cím vagy jelszó.');

        $authService->login('test@gmail.com', 'badPassword');
    }
}