<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

service('auth')->routes($routes);

// Rotas de Autenticação por Sessão para SPA / REST
$routes->group('api/auth', ['namespace' => 'App\Controllers\Api'], static function (RouteCollection $routes): void {
    $routes->post('login', 'Auth::login');
    $routes->post('logout', 'Auth::logout');
    $routes->get('me', 'Auth::me');
    $routes->post('register', 'Player::create');
});

$routes->group('api/players', ['namespace' => 'App\Controllers\Api'], static function (RouteCollection $routes): void {
    // Registro público de jogadores.
    $routes->post('/', 'Player::create');

    // Demais operações exigem autenticação por sessão do Shield.
    $routes->group('', ['filter' => 'api-session'], static function (RouteCollection $routes): void {
        $routes->get('/', 'Player::index');
        $routes->get('(:num)', 'Player::show/$1');
        $routes->put('(:num)', 'Player::update/$1');
        $routes->patch('(:num)', 'Player::update/$1');
        $routes->delete('(:num)', 'Player::delete/$1');
        $routes->post('(:num)/avatar', 'Player::uploadAvatar/$1');
    });
});

$routes->group('api/games', ['namespace' => 'App\Controllers\Api'], static function (RouteCollection $routes): void {
    // Catálogo público de jogos.
    $routes->get('/', 'Game::index');
    $routes->get('(:num)', 'Game::show/$1');

    // Cadastro, edição, exclusão e upload de capa: somente administradores
    // autenticados por sessão (com permissão `games.manage`).
    $routes->group('', ['filter' => 'api-session'], static function (RouteCollection $routes): void {
        $routes->post('/', 'Game::create');
        $routes->put('(:num)', 'Game::update/$1');
        $routes->patch('(:num)', 'Game::update/$1');
        $routes->delete('(:num)', 'Game::delete/$1');
        $routes->post('(:num)/cover', 'Game::uploadCover/$1');
    });
});
