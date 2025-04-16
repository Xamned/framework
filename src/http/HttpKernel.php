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
use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\http\resource\CrudResultInterface;
use xamned\framework\logger\enums\LogContext;

class HttpKernel implements HttpKernelInterface
{
    public function __construct(
        private readonly HTTPRouterInterface  $router,
        private readonly LoggerInterface $logger,
        private readonly ErrorHandlerInterface $errorHandler,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ContainerInterface $container,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $result = $this->router->dispatch($request);
            
            $response = $this->createResponse($result);

            $status = $response->getStatusCode();

            if ($status < 100 || $status > 599) {
                $response = $response->withStatus($this->getStatus($request));
            }
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
            'POST' => 201,
            'DELETE' => 204,
            default => 200,
        };
    }

    protected function createResponse(mixed $result): ResponseInterface
    {
        $response = $this->container->get(ResponseInterface::class);

        if (is_subclass_of($result, CrudResultInterface::class) === true) {
            $response = $response->withStatus($result->getStatusCode());
            $result = $result->getData();
        }

        if (is_array($result) === true || is_object($result) === true) {
            $response = $response->withHeader('Content-Type', 'application/json');
            $result = json_encode($result);
        }

        $response->getBody()->write($result);

        return $response;
    }

    protected function handleError(Throwable $error, ?int $code = null): ResponseInterface
    {
        $response = $this->container->get(ResponseInterface::class)->withStatus($code ?? $error->getCode());

        $this->eventDispatcher->trigger(LogContext::ATTACH->value, new Message('APP'));
        $this->logger->error($error->getMessage());

        if ($this->errorHandler->isCompatibleWith(MessageTypeEnum::JSON->value) === true) {
            $response = $response->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write($this->errorHandler->handle($error));

        return $response;
    }
}
