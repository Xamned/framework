<?php

namespace xamned\framework\http\router;

use Closure;
use xamned\framework\contracts\http\router\MiddlewareAssignable;
use xamned\framework\http\router\traits\MiddlewareAssignableTrait;

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

    public function getPathRegexPattern(): string
    {
        return addcslashes(preg_replace('/{.+?}/', '(.+?)', $this->path), '/');
    }

    public function getPathParams(): array
    {
        $matches = [];
        preg_match_all('/{(.+?)}/',  $this->path, $matches);
        return $matches[1];
    }
}