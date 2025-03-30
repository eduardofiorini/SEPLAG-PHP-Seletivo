<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Swagger::index');

$routes->group('auth', function ($routes) {
    $routes->post('/', 'Auth::index');
    $routes->post('registro', 'Auth::registro');
    $routes->get('perfil', 'Auth::perfil', ['filter' => 'jwt']);
    $routes->post('logout', 'Auth::logout');
});

$routes->get('api/v1/docs', 'Swagger::index');
$routes->get('api/v1/docs/generate', 'Swagger::generate');

$routes->group('api', ['filter' => 'jwt'], function ($routes) {
    $routes->get('data', 'ApiController::getData');
});