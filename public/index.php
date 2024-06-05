<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

if (isset($_SERVER['REQUEST_URI'])) {

    // This directory must be writable.
    $storage = __DIR__ . '/../storage/shieldon';

    $firewall = new \Shieldon\Firewall\Firewall();
    $firewall->configure($storage);

    // The base url for the control panel.
    $firewall->controlPanel('/firewall/panel/');

    $response = $firewall->run();

    if ($response->getStatusCode() !== 200) {
        $httpResolver = new \Shieldon\Firewall\HttpResolver();
        $httpResolver($response);
    }
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
