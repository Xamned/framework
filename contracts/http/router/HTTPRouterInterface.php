<?php

namespace framework\contracts\http\router;

use framework\http\router\Route;
use framework\http\router\RouteGroup;
use framework\http\exceptions\HttpNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

interface HTTPRouterInterface
{
    /**
     * Добавление маршрута для метода GET
     * 
     * @param  string $route путь
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    function get(string $route, string|callable $handler): Route;

    /**
     * Добавление маршрута для метода POST
     * 
     * @param  string $route путь
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    function post(string $route, string|callable $handler): Route;

    /**
     * Добавление маршрута для метода PUT
     * 
     * @param  string $route путь
     * @param  string|callable $handler обработчик, коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    function put(string $route, string|callable $handler): Route;

    /**
     * Добавление маршрута для метода PATCH
     * 
     * @param  string $route путь
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    function patch(string $route, string|callable $handler): Route;

    /**
     * Добавление маршрута для метода DELETE
     * 
     * @param  string $route путь
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    function delete(string $route, string|callable $handler): Route;

    /**
     * Добавление группы машрутов
     * 
     * Пример:
     * /api/v1/path
     * $router->group('api', function (HTTPRouterInterface $router) {
     *     
     *     $router->group('v1', function (HTTPRouterInterface $router) {
     * 
     *         $router->get('/path', SomeHandler::class . '::action');
     * 
     *     });
     *     
     * });
     * 
     * @param  string $name имя группы
     * @param  callable $set функция-сборщик маршрута группы
     * @return RouteGroup
     */
    function group(string $name, callable $set): RouteGroup;

    /**
     * Добавление маршрута для метода запроса
     * 
     * @param  string $method метод запроса
     * @param  string $route путь
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    function add(string $method, string $route, string|callable $handler): Route;

    /**
     * Диспетчеризация входящего запроса
     * 
     * @param  ServerRequestInterface $request объект запроса
     * @return mixed
     * @throws HttpNotFoundException если маршрут не зарегистрирован в конфигурации машрутов
     */
    function dispatch(ServerRequestInterface $request): mixed;
}
