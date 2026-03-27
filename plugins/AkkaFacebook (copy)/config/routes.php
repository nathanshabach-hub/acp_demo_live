<?php
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/** @var \Cake\Routing\RouteBuilder $routes */
$routes->plugin('AkkaFacebook', function (RouteBuilder $routes) {
    $routes->fallbacks(DashedRoute::class);
});
