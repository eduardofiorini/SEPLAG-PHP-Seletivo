<?php

namespace App\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class RateLimitFilter implements FilterInterface
{
    use ResponseTrait;
    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            $throttler = service('throttler');
            if ($throttler->check($request->getIPAddress(), 60, MINUTE) === false)
            {
                return service('response')
                    ->setStatusCode(429);
            }
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