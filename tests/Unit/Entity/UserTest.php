<?php declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testEveryAccountHasUserRole(): void
    {
        $user = (new User())->setRoles([]);

        $this->assertSame([User::ROLE_USER], $user->getRoles());
        $this->assertFalse($user->isAdmin());
    }

    public function testAdministratorIsAlsoRegularUser(): void
    {
        $user = (new User())->setRoles([User::ROLE_ADMIN]);

        $this->assertTrue($user->isAdmin());
        $this->assertSame([User::ROLE_ADMIN, User::ROLE_USER], $user->getRoles());
    }
}
