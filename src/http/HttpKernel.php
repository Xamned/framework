<?php

namespace xamned\framework\http;

use xamned\framework\contracts\ErrorHandlerInterface;
use xamned\framework\contracts\event_dispatcher\EventDispatcherInterface;
use xamned\framework\contracts\http\HttpKernelInterface;
use xamned\framework\contracts\http\router\HTTPRouterInterface;
use xamned\framework\contracts\logger\LoggerInterface;
use xamned\framework\http\exceptions\HttpException;
use xamned\framework\event_dispatcher\Message;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class HttpKernel implements HttpKernelInterface
{
    public function __construct(
        private readonly ResponseInterface $response,
        private readonly HTTPRouterInterface  $router,
        private readonly LoggerInterface $logger,
        private readonly ErrorHandlerInterface $errorHandler,
        private readonly EventDispatcherInterface $eventDispatcher
    ) { }

    /**
     * Обработка входящего запроса
     *
     * @return ResponseInterface объект ответа
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->response;
        try {
            $result = $this->router->dispatch($request);

            if (is_array($result) === true) {
                $result = json_encode($result);
                $response = $response->withHeader('Content-Type', 'application/json');
            }

            $response->getBody()->write($result);
        } catch (HttpException $e) {
            $response = $this->response->withStatus($e->getCode());
            $this->eventDispatcher->trigger('log.context.attach', new Message('APP'));
            $this->logger->error($e->getMessage());

            if ($this->errorHandler->isCompatibleWith(MessageTypeEnum::JSON->value) === true) {
                $response = $response->withHeader('Content-Type', 'application/json');
            }

            $response->getBody()->write($this->errorHandler->handle($e));
        } catch (Throwable $e) {
            $response = $this->response->withStatus(500);
            $this->eventDispatcher->trigger('log.context.attach', new Message('APP'));
            $this->logger->error($e->getMessage());

            if ($this->errorHandler->isCompatibleWith(MessageTypeEnum::JSON->value) === true) {
                $response = $response->withHeader('Content-Type', 'application/json');
            }

            $response->getBody()->write($this->errorHandler->handle($e));
        }

        return $response;
    }
}
