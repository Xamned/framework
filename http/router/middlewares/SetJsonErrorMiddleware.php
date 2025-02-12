<?php

namespace framework\http\router\middlewares;

use framework\contracts\ErrorHandlerInterface;
use framework\contracts\http\router\MiddlewareInterface;
use framework\http\MessageTypeEnum;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class SetJsonErrorMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, ResponseInterface $response, callable $next): void
    {
        $this->errorHandler->setResponseFormat(MessageTypeEnum::JSON->value);

        $next($request, $response);
    }
}
