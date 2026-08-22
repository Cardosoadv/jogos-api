<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

service('auth')->routes($routes);

$routes->group('api/players', ['namespace' => 'App\Controllers\Api'], static function (RouteCollection $routes): void {
    // Registro público de jogadores.
    $routes->post('/', 'PlayerController::create');

    // Demais operações exigem autenticação (Bearer access token).
    $routes->group('', ['filter' => 'tokens'], static function (RouteCollection $routes): void {
        $routes->get('/', 'PlayerController::index');
        $routes->get('(:num)', 'PlayerController::show/$1');
        $routes->put('(:num)', 'PlayerController::update/$1');
        $routes->patch('(:num)', 'PlayerController::update/$1');
        $routes->delete('(:num)', 'PlayerController::delete/$1');
        $routes->post('(:num)/avatar', 'PlayerController::uploadAvatar/$1');
    });
});
