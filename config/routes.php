<?php
declare(strict_types=1);

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return static function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    $routes->prefix('Admin', function (RouteBuilder $routes): void {
        $routes->connect('/', ['controller' => 'Admins', 'action' => 'login']);
        $routes->connect('/admins', ['controller' => 'Admins', 'action' => 'login']);
        $routes->fallbacks(DashedRoute::class);
    });

    $routes->prefix('Api', function (RouteBuilder $routes): void {
        $routes->fallbacks(DashedRoute::class);
    });

    $routes->scope('/', function (RouteBuilder $routes): void {
        $routes->connect('/', ['controller' => 'Homes', 'action' => 'index']);
        $routes->connect('/caterings', ['controller' => 'Caterings', 'action' => 'index']);
        $routes->connect('/trucks', ['controller' => 'Trucks', 'action' => 'index']);
        $routes->connect('/deals', ['controller' => 'Deals', 'action' => 'index']);
        $routes->connect('/truckmenucategories', ['controller' => 'Truckmenucategories', 'action' => 'index']);
        $routes->connect('/supporttickets', ['controller' => 'Supporttickets', 'action' => 'index']);
        $routes->connect('/truckinquiries', ['controller' => 'Truckinquiries', 'action' => 'index']);
        $routes->connect('/lots', ['controller' => 'Lots', 'action' => 'index']);
        $routes->connect('/lotownerpayments', ['controller' => 'Lotownerpayments', 'action' => 'index']);

        $routes->connect('/:slug', ['controller' => 'Trucks', 'action' => 'list'])
            ->setPass(['slug']);

        $routes->connect('/locations/:slug1/:slug2', ['controller' => 'Trucks', 'action' => 'list'])
            ->setPass(['slug1', 'slug2']);

        $routes->connect('/privacy_policy', ['controller' => 'Pages', 'action' => 'privacyPolicy']);
        $routes->connect('/terms_and_conditions', ['controller' => 'Pages', 'action' => 'termsAndConditions']);

        $routes->fallbacks(DashedRoute::class);
    });
};
