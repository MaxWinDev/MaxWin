<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\UserRegistrationService;
use App\Entity\User;
use App\Entity\Win;

class UserTest extends TestCase
{
    public function test_create_user(){
        $user = new User();

        $user->setBalance(50);
        $this->assertEquals(50, $user->getBalance());

        $user->setUsername("destroyeur");
        $this->assertEquals("destroyeur", $user->getUsername());
    }

    public function test_add_wins(){
        $win = new Win();
        $win2 = new Win();
        $user = new User();

        $user->addWin($win);
        $this->assertEquals(1, count($user->getWins()));

        $user->addWin($win2);
        $this->assertEquals(2, count($user->getWins()));
    }

    public function test_delete_wins(){
        $win = new Win();
        $win2 = new Win();
        $user = new User();

        $user->addWin($win);
        $this->assertEquals(1, count($user->getWins()));

        $user->removeWin($win);
        $this->assertEquals(0, count($user->getWins()));
    }
}

?>