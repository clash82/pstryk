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

    public function testRegularUserOpeningAdminPanelIsRedirectedToFrontPage(): void
    {
        $client = static::createClient();
        $this->loginAs($client, User::ROLE_USER);

        $client->request('GET', \sprintf('https://%s/zaplecze/users', self::HOST));

        $this->assertResponseRedirects(\sprintf('https://%s/', self::HOST));
    }

    public function testLogoutFromFrontPageEndsOnMainPage(): void
    {
        $client = static::createClient();
        $this->loginAs($client, User::ROLE_USER);

        $client->request('GET', \sprintf('https://%s/zaplecze/logout?from=front', self::HOST));

        $this->assertResponseRedirects(\sprintf('https://%s/', self::HOST));

        // the session is gone, so the admin panel asks for logging in again
        $client->request('GET', \sprintf('https://%s/zaplecze/users', self::HOST));
        $this->assertResponseRedirects(\sprintf('https://%s/zaplecze/login', self::HOST));
    }

    public function testRegularUserCanSeeFrontPage(): void
    {
        $client = static::createClient();
        $this->loginAs($client, User::ROLE_USER);

        $client->request('GET', \sprintf('https://%s', self::HOST));

        $this->assertResponseIsSuccessful();
    }

    public function testAdministratorCanBeChangedBackToRegularUserWhenAnotherAdministratorExists(): void
    {
        $client = static::createClient();
        // the logged in administrator is the "other" one that stays
        $this->loginAs($client, User::ROLE_ADMIN);
        $target = $this->createUser(User::ROLE_ADMIN);

        $crawler = $client->request('GET', \sprintf('https://%s/zaplecze/user/edit/%d', self::HOST, $target->getId()));
        $client->submit($crawler->selectButton('Dajesz')->form(['user[roles]' => User::ROLE_USER]));

        $this->assertResponseRedirects(\sprintf('https://%s/zaplecze/users', self::HOST));

        $updated = static::getContainer()->get(UserRepository::class)->find($target->getId());

        if (!$updated instanceof User) {
            $this->fail('The edited account should still exist');
        }

        $this->assertFalse($updated->isAdmin());
    }

    private function loginAs(KernelBrowser $client, string $role): void
    {
        $client->loginUser($this->createUser($role));
    }

    private function createUser(string $role): User
    {
        /** @var UserRepository $repository */
        $repository = static::getContainer()->get(UserRepository::class);

        $user = new User()
            ->setEmail(\sprintf('%s-%s@example.com', strtolower($role), bin2hex(random_bytes(4))))
            ->setPassword('not-used')
            ->setRoles([$role]);

        $repository->update($user);

        return $user;
    }
}
