<?php

namespace xamned\framework\http\router\middlewares;

use xamned\framework\contracts\http\router\MiddlewareInterface;
use xamned\framework\http\exceptions\HttpUnauthorizedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpBasicMidlleware implements MiddlewareInterface
{
    public function __construct(
        private readonly string $id,
        private readonly string $secret
    ) {

    }

    /**
     * @inheritDoc
     * @throws HttpUnauthorizedException
     */
    public function process(ServerRequestInterface $request, ResponseInterface $response, callable $next): void
    {
        if ($request->getHeader('Authorization')[0] === 'Basic ' . base64_encode($this->id . ':' . $this->secret)) {
            $next($request, $response);

            return;
        }

        throw new HttpUnauthorizedException();
    }
}