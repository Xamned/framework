<?php

namespace xamned\framework\http\router\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use xamned\framework\contracts\http\router\MiddlewareInterface;

class CorsMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, ResponseInterface $response, callable $next): void
    {
        $response = $response->withHeader("Access-Control-Allow-Origin", "*");
        $response = $response->withHeader("Access-Control-Allow-Methods", "*");
        $response = $response->withHeader("Access-Control-Allow-Headers", "*");

        $next($request, $response);
    }
}