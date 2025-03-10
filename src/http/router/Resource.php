<?php

namespace xamned\framework\http\router;

use xamned\framework\contracts\http\router\HTTPRouterInterface;

class Resource
{
    protected array $config = [
        'index' => [
            'method' => 'GET',
            'path' => '',
            'action' => 'actionList',
            'middleware' => [],
        ],
        'view' => [
            'method' => 'GET',
            'path' => "{/{:id|integer}",
            'action' => 'actionView',
            'middleware' => [],
        ],
        'create' => [
            'method' => 'POST',
            'path' => '',
            'action' => 'actionCreate',
            'middleware' => [],
        ],
        'put' => [
            'method' => 'PUT',
            'path' => "{/{:id|integer}",
            'action' => 'actionUpdate',
            'middleware' => [],
        ],
        'patch' => [
            'method' => 'PATCH',
            'path' => "{/{:id|integer}",
            'action' => 'actionPatch',
            'middleware' => [],
        ],
        'delete' => [
            'method' => 'DELETE',
            'path' => "{/{:id|integer}",
            'action' => 'actionDelete',
            'middleware' => [],
        ],
    ];

    public function __construct(
        protected string $name,
        protected string $controller,
        array $config = [],
    ) {
        if ($config !== []) {
            $this->config = $config;
        }
    }

    public function build(HTTPRouterInterface $router): void
    {
        foreach ($this->config as $route) {
            $router->add(
                $route['method'], 
                "{$this->name}{$route['path']}", 
                "{$this->controller}::{$route['action']}"
            )->addMiddlewares($route['middleware']);            
        }
    }
}
