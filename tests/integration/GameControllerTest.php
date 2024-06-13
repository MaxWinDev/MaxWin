<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HTTPFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;


class GameControllerTest extends WebTestCase
{
    private KernelBrowser | null $client = null;

    public function setUp() : void
    {
        $this->client = static::createClient();
        $crawler = $this->client->request('GET', '/auth/login');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('Se connecter')->form();
        $form['email'] = 'romain.fillot@gmail.com';
        $form['password'] = 'guerre007';
        $this->client->submit($form);
    }

    public function testClickToGame(){
        $crawler = $this->client->followRedirect();
        $this->assertEquals('/home/', $this->client->getRequest()->getPathInfo());

        // rouver le lien et cliquer dessus
        $link = $crawler->selectLink('Profile')->link();
        // $crawler = $this->client->click($link);

        // Vérifier qu'il y a bien une redirection
        // $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Vérifier si on est sur la bonne page 
        // $this->assertEquals('/game', $this->client->getRequest()->getPathInfo()); 
    }

}
