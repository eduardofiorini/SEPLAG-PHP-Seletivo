<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $allowedOrigins = ['http://localhost', 'http://127.0.0.1'];

        $origin = $request->getHeader('Origin') ? $request->getHeader('Origin')->getValue() : null;

        if ($origin && !in_array($origin, $allowedOrigins)) {
            return service('response')
                ->setStatusCode(403)
                ->setBody('CORS Bloqueado: Origem não permitida.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    }
}
