<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\UserRegistrationService;
use App\Entity\User;
use App\Entity\Win;

class WinTest extends TestCase
{
    public function test_create_win(){
        $win = new Win();
        $bet = "10.0";
        $amount = "10.5";

        $win->setBet($bet);
        $this->assertEquals($bet, $win->getBet());

        $win->setAmount($amount);
        $this->assertEquals($amount, $win->getAmount());
    }
}
