<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Swagger::index');

$routes->group('api/v1/docs', function ($routes) {
    $routes->get('/', 'Swagger::index');
    $routes->get('generate', 'Swagger::generate');
});

$routes->group('api/v1/auth', function ($routes) {
    $routes->post('/', 'Auth::index');
    $routes->post('registro', 'Auth::register');
    $routes->post('refresh', 'Auth::refresh', ['filter' => 'jwt']);
    $routes->post('logout', 'Auth::logout');
});

$routes->group('api/v1/unidades', ['filter' => 'jwt'], function ($routes) {
    $routes->get('/', 'Unidade::index');
    $routes->get('(:num)', 'Unidade::show/$1');
    $routes->post('/', 'Unidade::create');
    $routes->put('(:num)', 'Unidade::update/$1');
    $routes->delete('(:num)', 'Unidade::delete/$1');
});

$routes->group('api/v1/lotacoes', ['filter' => 'jwt'], function ($routes) {
    $routes->get('/', 'Lotacao::index');
    $routes->get('(:num)', 'Lotacao::show/$1');
    $routes->post('/', 'Lotacao::create');
    $routes->put('(:num)', 'Lotacao::update/$1');
    $routes->delete('(:num)', 'Lotacao::delete/$1');
});

$routes->group('api/v1/servidores-efetivos', ['filter' => 'jwt'], function ($routes) {
    $routes->get('/', 'ServidorEfetivo::index');
    $routes->get('(:num)', 'ServidorEfetivo::show/$1');
    $routes->post('/', 'ServidorEfetivo::create');
    $routes->put('(:num)', 'ServidorEfetivo::update/$1');
    $routes->delete('(:num)', 'ServidorEfetivo::delete/$1');
    $routes->post('(:num)/foto', 'ServidorEfetivo::uploadFoto');
    $routes->delete('(:num)/foto', 'ServidorEfetivo::deleteFoto');
    $routes->get('/buscar-por-nome', 'ServidorEfetivo::buscarPorNome');
    $routes->get('/unidade/(:num)', 'ServidorEfetivo::servidoresPorUnidade/$1');
});

$routes->group('api/v1/servidores-temporarios', ['filter' => 'jwt'], function ($routes) {
    $routes->get('/', 'ServidorTemporario::index');
    $routes->get('(:num)', 'ServidorTemporario::show/$1');
    $routes->post('/', 'ServidorTemporario::create');
    $routes->put('(:num)', 'ServidorTemporario::update/$1');
    $routes->delete('(:num)', 'ServidorTemporario::delete/$1');
    $routes->post('(:num)/foto', 'ServidorTemporario::uploadFoto');
    $routes->delete('(:num)/foto', 'ServidorTemporario::deleteFoto');
    $routes->get('/buscar-por-nome', 'ServidorEfetivo::buscarPorNome');
});

