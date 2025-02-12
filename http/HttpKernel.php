<?php

namespace framework\http;

use framework\contracts\ErrorHandlerInterface;
use framework\contracts\event_dispatcher\EventDispatcherInterface;
use framework\contracts\http\HttpKernelInterface;
use framework\contracts\http\router\HTTPRouterInterface;
use framework\contracts\logger\LoggerInterface;
use framework\http\exceptions\HttpException;
use framework\event_dispatcher\Message;
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
        } finally {
            $response = $response->withHeader('Content-Type', $request->getHeader('Content-Type'));
        }

        return $response;
    }
}
