<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    public function testLoginFail()
    {
        // Créer un client
        $client = static::createClient();

        // Récupérer la page de login
        $crawler = $client->request('GET', '/auth/login');

        // Vérifier que la page de login est chargée correctement
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Récupérer le formulaire de login
        $form = $crawler->selectButton('Se connecter')->form();

        // Remplir le formulaire avec les données de test
        $form['email'] = 'test.fail@gmail.com';
        $form['password'] = 'test';

        // Soumettre le formulaire
        $client->submit($form);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirection());

        // Suivre la redirection
        $crawler = $client->followRedirect();

        // Vérifier que la redirection mène à la page attendue
        // Vérifier que la page d'accueil est affichée après la connexion
        $this->assertEquals('/auth/login', $client->getRequest()->getPathInfo());
        $this->assertSelectorTextContains('p', 'Identifiant ou mot de passe incorrect !!!');
    }

    public function testLoginValid()
    {
         // Créer un client
         $client = static::createClient();

         // Récupérer la page de login
         $crawler = $client->request('GET', '/auth/login');
 
         // Vérifier que la page de login est chargée correctement
         $this->assertEquals(200, $client->getResponse()->getStatusCode());
 
         // Récupérer le formulaire de login
         $form = $crawler->selectButton('Se connecter')->form();
 
         // Remplir le formulaire avec les données de test
         $form['email'] = 'romain.fillot@gmail.com';
         $form['password'] = 'guerre007';
 
         // Soumettre le formulaire
         $client->submit($form);
 
         $this->assertEquals(302, $client->getResponse()->getStatusCode());
         $this->assertTrue($client->getResponse()->isRedirection());
 
         // Suivre la redirection
         $crawler = $client->followRedirect();
 
         // Vérifier que la redirection mène à la page attendue
         // Vérifier que la page d'accueil est affichée après la connexion
         $this->assertEquals('/home/', $client->getRequest()->getPathInfo());
         $this->assertSelectorTextContains('span', 'Max Win');
    }

    public function testAllPageWithoutConnexion(): void
    {
        $client = static::createClient();

        // Vérifier qu'il y a une redirection pour la page /game
        $crawler = $client->request('GET', '/game/');
        $this->assertTrue($client->getResponse()->isRedirection());
        $this->assertEquals('http://localhost/auth/login', $client->getResponse()->headers->get('Location'));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        
        // Vérifier qu'il y a une redirection pour la page /profil
        $crawler = $client->request('GET', '/profil/');
        $this->assertTrue($client->getResponse()->isRedirection());
        $this->assertEquals('http://localhost/auth/login', $client->getResponse()->headers->get('Location'));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

         // Vérifier qu'il y a une redirection pour la page /balance/withdraw
        $crawler = $client->request('GET', '/balance/withdraw');
        $this->assertTrue($client->getResponse()->isRedirection());
        $this->assertEquals('http://localhost/auth/login', $client->getResponse()->headers->get('Location'));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        
         // Vérifier qu'il y a une redirection pour la page /home
        $crawler = $client->request('GET', '/home');
        $this->assertTrue($client->getResponse()->isRedirection());
        $this->assertEquals('http://localhost/auth/login', $client->getResponse()->headers->get('Location'));
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }
}
