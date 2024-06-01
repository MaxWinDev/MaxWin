<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FirewallPanelController extends AbstractController
{
    #[Route('/firewall/panel', name: 'app_firewall_panel_1')]
    public function panel()
    {
        $panel = new \Shieldon\Firewall\Panel();

        // If your have `symfony/security-csrf` installed.
        $csrf = $this->container->get('security.csrf.token_manager');
        $token = $csrf->refreshToken('key')->getValue();

        $panel->csrf(['_token' => $token]);
        $panel->entry();
        exit;
    }

    #[Route('/firewall/panel/{class<\D+>}/{method<\D+>}', name: 'app_firewall_panel_2')]
    public function page(string $class, string $method): Response
    {
        $this->panel($class, $method);

        return new Response();
    }
}
