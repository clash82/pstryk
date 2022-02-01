<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeedControllerTest extends WebTestCase
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

        $client->request('GET', sprintf('https://%s/feed', self::HOST));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
