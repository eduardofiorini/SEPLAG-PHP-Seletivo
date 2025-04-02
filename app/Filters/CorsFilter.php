<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if(getenv('jwt.blockCors') == 'true'){
            $ip = getenv('jwt.privateKey');
            $allowedOrigins = ['http://localhost:8080', 'http://127.0.0.1:8080', $ip];

            $origin = $request->getHeader('Origin') ? $request->getHeader('Origin')->getValue() : null;

            if ($origin && !in_array($origin, $allowedOrigins)) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['error' => 'CORS bloqueado, origem não permitida.']);
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Headers', 'X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PATCH, PUT, DELETE');
    }
}
