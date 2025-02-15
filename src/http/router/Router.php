<?php

namespace xamned\framework\http\router;

use xamned\framework\http\exceptions\HttpNotFoundException;
use xamned\framework\contracts\http\router\HTTPRouterInterface;
use xamned\framework\contracts\http\router\MiddlewareAssignable;
use xamned\framework\contracts\http\router\MiddlewareInterface;
use xamned\framework\http\router\traits\MiddlewareAssignableTrait;
use xamned\framework\contracts\container\ContainerInterface;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Router implements HTTPRouterInterface, MiddlewareAssignable
{
    use MiddlewareAssignableTrait;

    protected array $middlewares = [];
    protected array $routes = [];
    protected array $routeGroups = [];

    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $route, string|callable $handler): Route
    {
        return $this->add('GET', $route, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function post(string $route, string|callable $handler): Route
    {
        return $this->add('POST', $route, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $route, string|callable $handler): Route
    {
        return $this->add('PUT', $route, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function patch(string $route, string|callable $handler): Route
    {
        return $this->add('PATCH', $route, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $route, string|callable $handler): Route
    {
        return $this->add('DELETE', $route, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function group(string $name, callable $set): RouteGroup
    {
        if ($this->routeGroups !== []) {
            /** @var RouteGroup */
            $parent = end($this->routeGroups);
        }

        $this->routeGroups[$name] = new RouteGroup($name, $parent ?? null);

        $set($this);

        return array_pop($this->routeGroups);
    }

    /**
     * Получение параметров запроса из маршрута
     * 
     * @param  string $route маршрут
     * Пример:
     * "/path?{firstNumber}{?secondNumber=900}"
     * @return array
     * Пример:
     * [
     *     [
     *         'name' => 'firstNumber',
     *         'required' => true,
     *         'default' => null,
     *     ],
     *     [
     *         'name' => 'secondNumber',
     *         'required' => false,
     *         'default' => 900,
     *     ],
     * ]
     */
    private function prepareParams(string $route): array
    {
        $params = [];

        preg_match_all('/{.*?}/', $route, $routeParts);
        $routeParts = $routeParts[0];

        foreach ($routeParts as $part) {
            $param = explode('=', trim($part, '{}'), 2);

            $template = [
                'name' => ltrim($param[0], '?'),
                'required' => str_starts_with($param[0], '?') === false,
                'default' => $param[1] ?? null,
            ];

            $params[] = $template;
        }

        return $params;
    }

    /**
     * Формирование массива параметров вызовов обработчика маршрута
     * 
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return array
     * Пример для callable:
     * [$handler, null]
     * Пример для string:
     * ['Неймспейс', 'метод'];
     */
    private function resolveHandler(callable|string $handler): array
    {
        if (is_callable($handler) === true) {
            return [$handler, null];
        }

        return explode('::', $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $method, string $route, string|callable $handler): Route
    {
        $routeGroupPath = '/';

        if ($this->routeGroups !== []) {
            /** @var RouteGroup */
            $parent = end($this->routeGroups);
            $routeGroupPath .= implode('/', array_keys($this->routeGroups)) . '/';
        }

        preg_match("/(?<=\/).*?(?=\?)/", $route, $path);

        $path = $routeGroupPath . $path[0];

        [$handler, $action] = $this->resolveHandler($handler);

        $this->routes[$method][$path] = new Route(
            $path, 
            $method,
            $this->prepareParams($route),
            $handler,
            $action,
            $parent ?? null
        );

        return $this->routes[$method][$path];
    }

    /**
     * Получение значений параметров запроса определенных для маршрута
     * 
     * Пример:
     * "/path?{firstNumber}{?secondNumber=900}"
     * "/path?firstNumber=700"
     * 
     * @param  ServerRequestInterface $request объект запроса
     * @param  Route $route объект маршрута
     * @return array
     * Пример:
     * [700, 900]
     * @throws InvalidArgumentException если в строке запроса не передан параметр объявленный как обязательный
     */
    private function mapParams(ServerRequestInterface $request, Route $route): array
    {
        $result = [];

        $queryParams = $request->getQueryParams();

        $pathParams = $this->mapPathParams($request, $route);

        foreach ($route->params as $param) {
            $name = $param['name'];

            if (isset($pathParams[$name]) === true) {
                $result[] = $pathParams[$name];
                continue;
            }

            if (isset($queryParams[$name]) === true) {
                $result[] = $queryParams[$name];
                continue;
            }

            if ($param['required'] === false) {
                $result[] = $param['default'];
                continue;
            }

            throw new InvalidArgumentException('В строке запроса не передан параметр объявленный как обязательный');
        }

        return $result;
    }

    private function mapPathParams(ServerRequestInterface $request, Route $route): array
    {
        $path = $request->getUri()->getPath();
        $pathParams = $route->getPathParams();
        $matches = [];

        $pattern = $route->getPathRegexPattern();
        preg_match("/$pattern/", $path, $matches);
        $matches = array_slice($matches, 1);

        if (count($pathParams) !== count($matches)) {
            throw new InvalidArgumentException('В строке запроса не передан параметр объявленный как обязательный');
        }

        return array_combine($pathParams, $matches);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(ServerRequestInterface $request): mixed
    {
        $route = $this->findRoute($request->getMethod(), $request->getUri()->getPath());

        $params = $this->mapParams($request, $route);

        $response = $this->container->get(ResponseInterface::class);

        $middlewares = array_merge($this->middlewares, $route->getMiddlewares());

        $this->applyMiddlewares($middlewares, $request, $response);

        if (is_callable($route->handler) === true) {
            return call_user_func($route->handler, ...$params);
        }

        return $this->container->call($route->handler, $route->action, $params);
    }

    private function findRoute(string $method, string $path): Route
    {
        if (isset($this->routes[$method][$path]) === true) {
            return $this->routes[$method][$path];
        }

        /** @var Route $route */
        foreach ($this->routes[$method] as $route) {
            $pattern = $route->getPathRegexPattern();

            if ((bool) preg_match("/$pattern/", $path) === true) {
                return $route;
            }
        }
        
        throw new HttpNotFoundException();
    }

    private function applyMiddlewares(array $middlewares, ServerRequestInterface $request, ResponseInterface $response): void
    {
        if ($middlewares === []) {
            return;
        }

        $currrentMiddleware = current($middlewares);

        $next = $this->constructNext($middlewares);

        if (is_callable($currrentMiddleware) === true) {
            call_user_func($currrentMiddleware, $request, $response, $next);
            return;
        }

        $this->container->get($currrentMiddleware)->process($request, $response, $next);
    }

    private function constructNext(array &$middlewares): callable
    {
        $currrentMiddleware = next($middlewares);

        if ($currrentMiddleware === false) {
            return function(): void {
                return;
            };
        }

        $middleware = $this->consctructMiddleware($currrentMiddleware);

        $next = $this->constructNext($middlewares);

        return function(ServerRequestInterface $request, ResponseInterface $response) use ($middleware, $next): void {
            call_user_func($middleware, $request, $response, $next);
        };
    }

    private function consctructMiddleware(string|callable $middleware): callable
    {
        if (is_callable($middleware) === true) {
            return $middleware;
        }

        /** @var MiddlewareInterface */
        $middleware = $this->container->get($middleware);

        return function(ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($middleware): void {
            $middleware->process($request, $response, $next);
        };
    }
}
