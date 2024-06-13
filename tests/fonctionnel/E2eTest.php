<?php

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

// require __DIR__.'/vendor/autoload.php'; // Composer's autoloader

// $client = Client::createChromeClient();
// $client = static::createPantherClient();
// Or, if you care about the open web and prefer to use Firefox
// $client = Client::createFirefoxClient();

// $client->request('GET', 'http://127.0.0.1:8000/game/'); // Yes, this website is 100% written in JavaScript
// $client->clickLink('Getting started');

// // Wait for an element to be present in the DOM (even if hidden)
// $crawler = $client->waitFor('#installing-the-framework');
// // Alternatively, wait for an element to be visible
// $crawler = $client->waitForVisibility('#installing-the-framework');

// echo $crawler->filter('#installing-the-framework')->text();
// $client->takeScreenshot('screen.png'); // Yeah, screenshot!
