<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\User;

final class UserPasswordTest extends TestCase
{
    public function testSetAndVerifyPassword(): void
    {
        $u = new User([
            'username' => 'demo',
            'name'     => 'Demo User',
            'email'    => 'demo@example.com',
            'role'     => 'employee',
            'employee_code' => '1234567',
        ]);

        $u->setPasswordPlain('secret123');

        $this->assertTrue($u->verifyPassword('secret123'));
        $this->assertFalse($u->verifyPassword('wrongpass'));
    }
}
