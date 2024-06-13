<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HTTPFoundation\Response;

class GameControllerTest extends WebTestCase
{
    public function testAllPageWithoutConnexion(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/game/');

        // Vérifier qu'il y a une redirection
        $this->assertTrue($client->getResponse()->isRedirection());
        $this->assertEquals('http://localhost/auth/login', $client->getResponse()->headers->get('Location'));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        
        $crawler = $client->request('GET', '/profil/');
        $this->assertTrue($client->getResponse()->isRedirection());
        $this->assertEquals('http://localhost/auth/login', $client->getResponse()->headers->get('Location'));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', '/balance/withdraw');
        $this->assertTrue($client->getResponse()->isRedirection());
        $this->assertEquals('http://localhost/auth/login', $client->getResponse()->headers->get('Location'));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        
        $crawler = $client->request('GET', '/home');
        $this->assertTrue($client->getResponse()->isRedirection());
        $this->assertEquals('http://localhost/auth/login', $client->getResponse()->headers->get('Location'));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        // $this->assertSelectorTextContains('div', 'hdr_auto');
        // $this->assertSelectorTextContains('div', 'autorenew');
        // $this->assertSelectorTextContains('div', 'offline_bolt');
    }

    
}
