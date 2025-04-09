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
use xamned\framework\http\resource\responses\BaseResponse;
use xamned\framework\http\resource\responses\JsonResponse;

class HttpKernel implements HttpKernelInterface
{
    public function __construct(
        private readonly ResponseInterface $response,
        private readonly HTTPRouterInterface  $router,
        private readonly LoggerInterface $logger,
        private readonly ErrorHandlerInterface $errorHandler,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $result = $this->router->dispatch($request);
            
            $response = $this->createResponse($result, $this->getStatus($request));
        } catch (HttpException $e) {
            $response = $this->handleError($e);
        } catch (Throwable $e) {
            $response = $this->handleError($e, 500);
        }

        return $response;
    }

    protected function getStatus(ServerRequestInterface $request): int
    {
        $method = strtoupper($request->getMethod());
        return match($method) {
            'GET' => 200,
            'POST' => 201,
            'PUT' => 200,
            'PATCH' => 200,
            'DELETE' => 204,
        };
    }

    protected function createResponse(mixed $result, int $code): ResponseInterface
    {
        $response = $this->response;

        if ($result instanceof JsonResponse === true) {
            $result = $result->data;
        }

        if ($result instanceof BaseResponse === true) {
            $result = $result->data;
            $code = $result->code;
        }

        if (is_array($result) === true) {
            $result = json_encode($result);
            $response = $response->withHeader('Content-Type', 'application/json');
        }

        $response = $response->withStatus($code);

        $response->getBody()->write($result);

        return $response;
    }

    protected function handleError(Throwable $error, ?int $code = null): ResponseInterface
    {
        $response = $this->response->withStatus($code ?? $error->getCode());

        $this->eventDispatcher->trigger('log.context.attach', new Message('APP'));
        $this->logger->error($error->getMessage());

        if ($this->errorHandler->isCompatibleWith(MessageTypeEnum::JSON->value) === true) {
            $response = $response->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write($this->errorHandler->handle($error));

        return $response;
    }
}
