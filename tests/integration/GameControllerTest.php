<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HTTPFoundation\Response;

class GameControllerTest extends WebTestCase
{
    public function testPageGame(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/game/');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('div', 'hdr_auto');
        $this->assertSelectorTextContains('div', 'autorenew');
        $this->assertSelectorTextContains('div', 'offline_bolt');
    }
}
