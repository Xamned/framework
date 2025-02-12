<?php

namespace framework\http\router;

use Closure;
use framework\contracts\http\router\MiddlewareAssignable;
use framework\http\router\traits\MiddlewareAssignableTrait;

class Route implements MiddlewareAssignable
{
    use MiddlewareAssignableTrait;

    public function __construct(
        public readonly string $path,
        public readonly string $method,
        public readonly array $params,
        public readonly string|Closure $handler,
        public readonly ?string $action,
        protected ?RouteGroup $parent = null,
        protected array $middlewares = [],
    ) {
    }

    public function getMiddlewares(): array
    {
        if ($this->parent === null) {
            return $this->middlewares;
        }

        return array_merge($this->parent->getMiddlewares(), $this->middlewares);
    }
}