<?php

namespace App\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Exception;

class JWTAuthFilter implements FilterInterface
{
    use ResponseTrait;

    public function before(RequestInterface $request, $arguments = null)
    {
        $key = getenv('jwt.privateKey');
        $authHeader = $request->getHeaderLine('Authorization');
        $token = null;

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // Se não houver token
        if (is_null($token)) {
            $response = service('response');
            $response->setJSON([
                'status' => 401,
                'error' => 'Acesso não autorizado. Token não fornecido.'
            ]);
            $response->setStatusCode(401);
            return $response;
        }

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            $request->decoded = $decoded;
        } catch (Exception $e) {
            $response = service('response');
            $response->setJSON([
                'status' => 401,
                'error' => 'Token inválido ou expirado.'
            ]);
            $response->setStatusCode(401);
            return $response;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nada a fazer após a execução
    }
}