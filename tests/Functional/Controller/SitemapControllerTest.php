<?php declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SitemapControllerTest extends WebTestCase
{
    private const HOST = 'localhost';

    protected function setUp(): void
    {
        $_SERVER['SERVER_NAME'] = self::HOST;
    }

    public function testIndex(): void
    {
        $client = static::createClient();

        $client->request('GET', \sprintf('https://%s/sitemap', self::HOST));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testIndexXml(): void
    {
        $client = static::createClient();

        $client->request('GET', \sprintf('https://%s/sitemap.xml', self::HOST));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }
}
