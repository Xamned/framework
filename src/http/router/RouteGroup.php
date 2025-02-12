<?php

namespace xamned\framework\http\router;

use xamned\framework\contracts\http\router\MiddlewareAssignable;
use xamned\framework\http\router\traits\MiddlewareAssignableTrait;

class RouteGroup implements MiddlewareAssignable
{
    use MiddlewareAssignableTrait;

    public function __construct(
        public readonly string $name,
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