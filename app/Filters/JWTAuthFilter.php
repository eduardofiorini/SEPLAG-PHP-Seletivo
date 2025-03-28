<?php

namespace App\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Exception;

class JWTAuthFilter implements FilterInterface
{
    use ResponseTrait;
    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            helper('jwt');
            jwtValidateRequest(jwtRequest($request->getServer('HTTP_AUTHORIZATION')));
            return $request;
        } catch (Exception $e) {
            return service('response')
                ->setJSON(['error' => $e->getMessage()])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {

    }
}