<?php

namespace xamned\framework\http\router\middlewares;

use xamned\framework\contracts\error_handler\ErrorHandlerInterface;
use xamned\framework\contracts\http\router\MiddlewareInterface;
use xamned\framework\error_handler\MessageTypeEnum;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class SetJsonErrorMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler
    ) {}

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, ResponseInterface $response, callable $next): void
    {
        $this->errorHandler->setResponseFormat(MessageTypeEnum::JSON);

        $next($request, $response);
    }
}
