<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MobileFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Pour le moment, on laisse passer toutes les requêtes
        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}




