<?php

namespace xamned\framework\http\router\middlewares;

use xamned\framework\contracts\http\router\MiddlewareInterface;
use xamned\framework\http\exceptions\HttpUnauthorizedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class XApiKeyMidlleware implements MiddlewareInterface
{
    public function __construct(
        private readonly string $xApiKey,
    ) {
    }

    /**
     * @inheritDoc
     * @throws HttpUnauthorizedException
     */
    public function process(ServerRequestInterface $request, ResponseInterface $response, callable $next): void
    {
        $token = $request->getHeader('X-Api-Key')[0] ?? null;

        if ($token === $this->xApiKey) {
            $next($request, $response);
            return;
        }

        throw new HttpUnauthorizedException();
    }
}