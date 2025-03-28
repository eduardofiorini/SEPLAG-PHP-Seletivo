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

        $origin = $request->getServer('HTTP_ORIGIN');

        if (!in_array($origin, $allowedOrigins)) {
            return service('response')
                ->setStatusCode(403)
                ->setBody('CORS Bloqueado: Origem não permitida.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {

    }
}
