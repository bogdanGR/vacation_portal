<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\User;

final class UserModelTest extends TestCase
{
    public function testSettersNormalize(): void
    {
        $u = new User();
        $u->setUsername('  Alice ');
        $u->setName('  Alice Cooper ');
        $u->setEmail('  ALICE@EXAMPLE.COM ');
        $u->setEmployeeCode('0070001');
        $u->setRole('employee');

        $this->assertSame('Alice', $u->getUsername());
        $this->assertSame('Alice Cooper', $u->getName());
        $this->assertSame('alice@example.com', $u->getEmail());
        $this->assertSame('0070001', $u->getEmployeeCode());
        $this->assertSame('employee', $u->getRole());
    }

    public function testInvalidRoleThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $u = new User();
        $u->setRole('boss');
    }

    public function testToSessionStripsSensitiveFields(): void
    {
        $u = new User([
            'username' => 'jdoe',
            'name' => 'John Doe',
            'email' => 'jdoe@example.com',
            'role' => 'manager',
            'employee_code' => null,
        ]);

        $arr = $u->toSession();

        $this->assertArrayHasKey('username', $arr);
        $this->assertArrayNotHasKey('password', $arr);
        $this->assertSame('manager', $arr['role']);
    }
}
