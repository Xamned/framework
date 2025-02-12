<?php

namespace framework\http\router\middlewares;

use framework\contracts\event_dispatcher\EventDispatcherInterface;
use framework\contracts\http\router\MiddlewareInterface;
use framework\contracts\logger\LoggerInterface;
use framework\event_dispatcher\Message;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class RequestLogMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private EventDispatcherInterface $eventDispatcher,
    ) {

    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, ResponseInterface $response, callable $next): void
    {
        $this->eventDispatcher->trigger('log.context.attach', new Message('APP'));
        $this->logger->debug('Выполнено обращение методом ' . $request->getMethod() . ' к эндпоинту ' . $request->getUri()->getPath());

        $next($request, $response);
    }
}