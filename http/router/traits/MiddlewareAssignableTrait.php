<?php

namespace framework\http\router\traits;

use framework\contracts\http\router\MiddlewareInterface;

trait MiddlewareAssignableTrait
{
    /**
     * {@inheritdoc}
     */
    public function addMiddleware(callable|string $middleware): void
    {
        if (is_callable($middleware) === true) {
            $this->middlewares[] = $middleware;
            return;
        }

        if (is_subclass_of($middleware, MiddlewareInterface::class) === true) {
            $this->middlewares[] = $middleware;
            return;
        }

        throw new \Error("$middleware не соответствует интерфейсу - " . MiddlewareInterface::class);
    }

    /**
     * @param string[] $middlewares
     * @return void
     */
    public function addMiddlewares(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            $this->addMiddleware($middleware);
        }
    }
}