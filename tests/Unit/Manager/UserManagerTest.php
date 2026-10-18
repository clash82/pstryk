<?php declare(strict_types=1);

namespace App\Tests\Unit\Manager;

use App\Entity\User;
use App\Manager\UserManager;
use App\Repository\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManagerTest extends TestCase
{
    private UserRepository&MockObject $repository;

    private UserPasswordHasherInterface&MockObject $hasher;

    private UserManager $manager;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepository::class);
        $this->hasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->manager = new UserManager($this->repository, $this->hasher);
    }

    public function testSaveHashesNewPassword(): void
    {
        $user = new User();

        $this->hasher->expects($this->once())->method('hashPassword')->with($user, 'secret123')->willReturn('hashed');
        $this->repository->expects($this->once())->method('update')->with($user);

        $this->manager->save($user, 'secret123');

        $this->assertSame('hashed', $user->getPassword());
    }

    public function testSaveKeepsPasswordWhenNoneGiven(): void
    {
        $user = (new User())->setPassword('old-hash');

        $this->hasher->expects($this->never())->method('hashPassword');
        $this->repository->expects($this->once())->method('update')->with($user);

        $this->manager->save($user, '');

        $this->assertSame('old-hash', $user->getPassword());
    }

    public function testCannotDeleteOwnAccount(): void
    {
        $user = $this->createUser(1, [User::ROLE_USER]);
        $this->repository->method('getById')->willReturn($user);
        $this->repository->expects($this->never())->method('delete');

        $this->expectException(\DomainException::class);

        $this->manager->deleteById(1, $user);
    }

    public function testCannotDeleteLastAdministrator(): void
    {
        $admin = $this->createUser(2, [User::ROLE_ADMIN]);
        $this->repository->method('getById')->willReturn($admin);
        $this->repository->method('countAdmins')->willReturn(1);
        $this->repository->expects($this->never())->method('delete');

        $this->expectException(\DomainException::class);

        $this->manager->deleteById(2, $this->createUser(3, [User::ROLE_USER]));
    }

    public function testDeletesOtherAccount(): void
    {
        $admin = $this->createUser(2, [User::ROLE_ADMIN]);
        $this->repository->method('getById')->willReturn($admin);
        $this->repository->method('countAdmins')->willReturn(2);
        $this->repository->expects($this->once())->method('delete')->with($admin);

        $this->manager->deleteById(2, $this->createUser(3, [User::ROLE_ADMIN]));
    }

    /**
     * @param string[] $roles
     */
    private function createUser(int $id, array $roles): User
    {
        $user = (new User())->setRoles($roles);

        (new \ReflectionProperty(User::class, 'id'))->setValue($user, $id);

        return $user;
    }
}
