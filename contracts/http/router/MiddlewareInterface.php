<?php

namespace framework\contracts\http\router;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

interface MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return void
     */
    public function process(ServerRequestInterface $request, ResponseInterface $response, callable $next): void;
}

