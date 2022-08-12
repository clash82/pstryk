<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeedControllerTest extends WebTestCase
{
    private const HOST = 'localhost';

    protected function setUp(): void
    {
        $_SERVER['SERVER_NAME'] = self::HOST;
    }

    public function testIndex(): void
    {
        $client = static::createClient();

        $client->request('GET', sprintf('https://%s/feed', self::HOST));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }
}
