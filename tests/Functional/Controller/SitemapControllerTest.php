<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SitemapControllerTest extends WebTestCase
{
    const HOST = 'localhost';

    public function setUp(): void
    {
        $_SERVER['SERVER_NAME'] = self::HOST;
    }

    public function testIndex()
    {
        /** @var KernelBrowser $client */
        $client = static::createClient();

        $client->request('GET', sprintf('https://%s/sitemap', self::HOST));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testIndexXml()
    {
        /** @var KernelBrowser $client */
        $client = static::createClient();

        $client->request('GET', sprintf('https://%s/sitemap.xml', self::HOST));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
