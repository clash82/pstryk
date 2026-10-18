<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private const string HOST = 'localhost';

    protected function setUp(): void
    {
        $_SERVER['SERVER_NAME'] = self::HOST;
    }

    public function testAnonymousUserIsSentToLogin(): void
    {
        $client = static::createClient();

        $client->request('GET', \sprintf('https://%s/zaplecze/users', self::HOST));

        $this->assertResponseRedirects(\sprintf('https://%s/zaplecze/login', self::HOST));
    }

    public function testAdministratorSeesUserList(): void
    {
        $client = static::createClient();
        $this->loginAs($client, User::ROLE_ADMIN);

        $client->request('GET', \sprintf('https://%s/zaplecze/users', self::HOST));

        $this->assertResponseIsSuccessful();
    }

    public function testAdministratorSeesUserForm(): void
    {
        $client = static::createClient();
        $this->loginAs($client, User::ROLE_ADMIN);

        $client->request('GET', \sprintf('https://%s/zaplecze/user/add', self::HOST));

        $this->assertResponseIsSuccessful();
    }

    public function testRegularUserHasNoAccessToAdminPanel(): void
    {
        $client = static::createClient();
        $this->loginAs($client, User::ROLE_USER);

        $client->request('GET', \sprintf('https://%s/zaplecze/users', self::HOST));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testRegularUserCanSeeFrontPage(): void
    {
        $client = static::createClient();
        $this->loginAs($client, User::ROLE_USER);

        $client->request('GET', \sprintf('https://%s', self::HOST));

        $this->assertResponseIsSuccessful();
    }

    private function loginAs(KernelBrowser $client, string $role): void
    {
        /** @var UserRepository $repository */
        $repository = static::getContainer()->get(UserRepository::class);

        $user = (new User())
            ->setEmail(\sprintf('%s-%s@example.com', strtolower($role), bin2hex(random_bytes(4))))
            ->setPassword('not-used')
            ->setRoles([$role]);

        $repository->update($user);

        $client->loginUser($user);
    }
}
