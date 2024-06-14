<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HTTPFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class LinKNavBarTest extends WebTestCase
{
    private KernelBrowser | null $client = null;

    public function setUp() : void
    {
        $this->client = static::createClient();
        $crawler = $this->client->request('GET', '/auth/login');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('Se connecter')->form();
        $form['email'] = 'alexis.carreau16@gmail.com';
        $form['password'] = 'password';
        $this->client->submit($form);
    }

    public function testLinkProfil(): void
    {
        $crawler = $this->client->followRedirect();
        $this->assertEquals('/home/', $this->client->getRequest()->getPathInfo());

        // $this->assertEquals('/home/', $client->getRequest()->getPathInfo());
        $this->assertSelectorTextContains('span', 'Max Win');

        // rouver le lien et cliquer dessus
        $link = $crawler->selectLink('Profile')->link();
        $crawler = $this->client->click($link);

        // Vérifier qu'il y a bien une redirection
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Vérifier si on est sur la bonne page 
        $this->assertEquals('/profil', $this->client->getRequest()->getPathInfo()); 
    }

    public function testLinkLogo(): void
    {
        $crawler = $this->client->followRedirect();
        $this->assertEquals('/home/', $this->client->getRequest()->getPathInfo()); 

        $link = $crawler->selectLink('Max Win')->link();
        $crawler = $this->client->click($link);

        // Vérifier qu'il y a bien une redirection
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Vérifier si on est sur la bonne page 
        $this->assertEquals('/home/', $this->client->getRequest()->getPathInfo()); 
    }
}

