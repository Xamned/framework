<?php

namespace framework\tests;

use framework\contracts\container\ContainerInterface;
use framework\error_handler\http\exceptions\HttpNotFoundException;
use framework\contracts\http\router\HTTPRouterInterface;
use framework\contracts\http\router\MiddlewareInterface;
use framework\http\router\Route;
use framework\http\router\RouteGroup;
use framework\http\router\Router;
use modules\calculation_mode_generator\controllers\Controller;
use modules\calculation_mode_generator\controllers\ApiController;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Tests\Support\UnitTester;

class RouterTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;

    protected function _before()
    {
    }

    /** @dataProvider getProvider
     * @throws Exception
     */
    public function testGet($path, $handler, $expected)
    {
        $di = $this->createStub(ContainerInterface::class);
        $router = new Router($di);

        $actual = $router->get($path, $handler);

        $this->assertEquals($expected, $actual);
    }

    /** @dataProvider postProvider
     * @throws Exception
     */
    public function testPost($path, $handler, $expected)
    {
        $di = $this->createStub(ContainerInterface::class);
        $router = new Router($di);

        $actual = $router->post($path, $handler);

        $this->assertEquals($expected, $actual);
    }

    /** @dataProvider putProvider
     * @throws Exception
     */
    public function testPut($path, $handler, $expected)
    {
        $di = $this->createStub(ContainerInterface::class);
        $router = new Router($di);

        $actual = $router->put($path, $handler);

        $this->assertEquals($expected, $actual);
    }

    /** @dataProvider patchProvider
     * @throws Exception
     */
    public function testPatch($path, $handler, $expected)
    {
        $di = $this->createStub(ContainerInterface::class);
        $router = new Router($di);

        $actual = $router->patch($path, $handler);

        $this->assertEquals($expected, $actual);
    }

    /** @dataProvider deleteProvider
     * @throws Exception
     */
    public function testDelete($path, $handler, $expected)
    {
        $di = $this->createStub(ContainerInterface::class);
        $router = new Router($di);

        $actual = $router->delete($path, $handler);

        $this->assertEquals($expected, $actual);
    }

    /** @dataProvider addProvider
     * @throws Exception
     */
    public function testAdd($method, $path, $handler, $middlewares, $expected)
    {
        $di = $this->createStub(ContainerInterface::class);
        $router = new Router($di);

        foreach ($middlewares as $middleware) {
            $router->addMiddleware($middleware);
        }

        $actual = $router->add($method, $path, $handler);

        $this->assertEquals($expected, $actual);
    }

    /** @dataProvider groupProvider
     * @throws Exception
     */
    public function testGroup($name, $set, $expected)
    {
        $di = $this->createStub(ContainerInterface::class);

        $router = new Router($di);

        $actual = $router->group($name, $set);

        $this->assertEquals($expected, $actual);
    }

    /** @dataProvider dispatchProvider
     * @throws Exception|HttpNotFoundException
     */
    public function testDispatch($request, $configure, $expected, $exception = null)
    {
        if ($exception !== null) {
            $this->expectException($exception);
        }

        $di = $this->createConfiguredStub(ContainerInterface::class, [
            'get' => $this->createStub(ResponseInterface::class),
            'call' => $expected,
        ]);

        $router = new Router($di);

        $configure($router);

        $actual = $router->dispatch($request);

        $this->assertEquals($expected, $actual);
    }

    /** @dataProvider addMiddlewareProvider */
    public function testAddMiddleware($middleware, $expected = '')
    {
        $error = '';

        $di = $this->createStub(ContainerInterface::class);

        $router = new Router($di);

        try {
            $router->addMiddleware($middleware);
        } catch (\Error $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals($expected, $error);
    }

    public static function methodProvider($method): array
    {
        return [
            'test with valid data' => [
                'path' => '/calculator/calculate-modes?{firstNumber=500}{?secondNumber=700}',
                'handler' => Controller::class . '::actionCalculateModes',
                'expected' => new Route(
                    path: '/calculator/calculate-modes',
                    method: $method,
                    params: [
                        [
                            'name' => 'firstNumber',
                            'required' => true,
                            'default' => 500,
                        ],
                        [
                            'name' => 'secondNumber',
                            'required' => false,
                            'default' => 700,
                        ]
                    ],
                    handler: Controller::class,
                    action: 'actionCalculateModes',
                    middlewares: [],
                ),
            ],
        ];
    }

    public static function getProvider(): array
    {
        return static::methodProvider('GET');
    }

    public static function postProvider(): array
    {
        return static::methodProvider('POST');
    }

    public static function putProvider(): array
    {
        return static::methodProvider('PUT');
    }

    public static function patchProvider(): array
    {
        return static::methodProvider('PATCH');
    }

    public static function deleteProvider(): array
    {
        return static::methodProvider('DELETE');
    }

    /**
     * @throws Exception
     */
    public static function addProvider(): array
    {
        return [
            'test where route handler is class' => [
                'method' => 'POST',
                'path' => '/calculator/calculate-modes?{firstNumber}{?secondNumber=740}',
                'handler' => Controller::class . '::actionCalculateModes',
                'middlewares' => [],
                'expected' => new Route(
                    path: '/calculator/calculate-modes',
                    method: 'POST',
                    params: [
                        [
                            'name' => 'firstNumber',
                            'required' => true,
                            'default' => null,
                        ],
                        [
                            'name' => 'secondNumber',
                            'required' => false,
                            'default' => 740,
                        ]
                    ],
                    handler: Controller::class,
                    action: 'actionCalculateModes',
                    middlewares: [],
                ),
            ],
            'test where route handler is callable' => [
                'method' => 'POST',
                'path' => '/calculator/calculate-modes?{firstNumber}{?secondNumber=740}',
                'handler' => fn() => 'hello',
                'middlewares' => [],
                'expected' => new Route(
                    path: '/calculator/calculate-modes',
                    method: 'POST',
                    params: [
                        [
                            'name' => 'firstNumber',
                            'required' => true,
                            'default' => null,
                        ],
                        [
                            'name' => 'secondNumber',
                            'required' => false,
                            'default' => 740,
                        ]
                    ],
                    handler: fn() => 'hello',
                    action: null,
                    middlewares: [],
                ),
            ],
        ];
    }

    public static function groupProvider(): array
    {
        return [
            'test with valid data' => [
                'name' => 'api',
                'set' => function (HTTPRouterInterface $router): void {
                    $router->group('v1', function (HTTPRouterInterface $router): void {

                        $router->get('/calculator/calculate-modes?{firstNumber}{?secondNumber=700}', ApiController::class . '::actionIndex');
                    });
                },
                'expected' => new RouteGroup('api', []),
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public static function dispatchProvider(): array
    {
        return [
            'test where route handler is callable' => [
                'request' => static::createConfiguredStub(ServerRequestInterface::class, [
                    'getMethod' => 'GET',
                    'getUri' => static::createConfiguredStub(UriInterface::class, [
                        'getPath' => '/calculator/calculate-modes',
                    ]),
                    'getQueryParams' => [
                        'firstNumber' => 500,
                        'secondNumber' => 700,
                    ],
                ]),
                'configure' => function (HTTPRouterInterface $router): void {
                    $router->get(
                        '/calculator/calculate-modes?{firstNumber=500}{?secondNumber=700}',
                        function ($num1, $num2) {
                            return "First number - {$num1}, Second number - {$num2}";
                        }
                    );
                },
                'expected' => "First number - 500, Second number - 700",
            ],
            'test where route configured by route group' => [
                'request' => static::createConfiguredStub(ServerRequestInterface::class, [
                    'getMethod' => 'GET',
                    'getUri' => static::createConfiguredStub(UriInterface::class, [
                        'getPath' => '/api/v1/calculator/calculate-modes',
                    ]),
                    'getQueryParams' => [
                        'firstNumber' => 500,
                        'secondNumber' => 700,
                    ],
                ]),
                'configure' => function (HTTPRouterInterface $router): void {
                    $router->group('api', function (HTTPRouterInterface $router): void {
                        $router->group('v1', function (HTTPRouterInterface $router): void {
                            $router->get(
                                '/calculator/calculate-modes?{firstNumber}{?secondNumber=700}',
                                function ($num1, $num2) {
                                    return "First number - {$num1}, Second number - {$num2}";
                                }
                            );
                        });
                    });
                },
                'expected' => "First number - 500, Second number - 700",
            ],
            'test where route handler is class' => [
                'request' => static::createConfiguredStub(ServerRequestInterface::class, [
                    'getMethod' => 'GET',
                    'getUri' => static::createConfiguredStub(UriInterface::class, [
                        'getPath' => '/calculator/calculate-modes',
                    ]),
                    'getQueryParams' => [
                        'firstNumber' => 500,
                        'secondNumber' => 700,
                    ],
                ]),
                'configure' => function (HTTPRouterInterface $router): void {
                    $router->get(
                        '/calculator/calculate-modes?{firstNumber=500}{?secondNumber=700}',
                        Controller::class . '::actionCalculateModes'
                    );
                },
                'expected' => "First number - 500, Second number - 700",
            ],
            'test catch HttpNotFoundException' => [
                'request' => static::createConfiguredStub(ServerRequestInterface::class, [
                    'getMethod' => 'GET',
                    'getUri' => static::createConfiguredStub(UriInterface::class, [
                        'getPath' => '/calculator',
                    ]),
                    'getQueryParams' => [
                        'firstNumber' => 500,
                        'secondNumber' => 700,
                    ],
                ]),
                'configure' => function (HTTPRouterInterface $router): void {
                    $router->get(
                        '/calculator/calculate-modes?{firstNumber=500}{?secondNumber=700}',
                        function ($num1, $num2) {
                            return "First number - {$num1}, Second number - {$num2}";
                        }
                    );
                },
                'expected' => "First number - 500, Second number - 700",
                'exception' => HttpNotFoundException::class,
            ],
            'test catch InvalidArgumentException' => [
                'request' => static::createConfiguredStub(ServerRequestInterface::class, [
                    'getMethod' => 'GET',
                    'getUri' => static::createConfiguredStub(UriInterface::class, [
                        'getPath' => '/calculator/calculate-modes',
                    ]),
                    'getQueryParams' => [
                        'secondNumber' => 700,
                    ],
                ]),
                'configure' => function (HTTPRouterInterface $router): void {
                    $router->get(
                        '/calculator/calculate-modes?{firstNumber=500}{?secondNumber=700}',
                        function ($num1, $num2) {
                            return "First number - {$num1}, Second number - {$num2}";
                        }
                    );
                },
                'expected' => "First number - 500, Second number - 700",
                'exception' => \InvalidArgumentException::class,
            ],
            'test apply middlewares' => [
                'request' => static::createConfiguredStub(ServerRequestInterface::class, [
                    'getMethod' => 'GET',
                    'getUri' => static::createConfiguredStub(UriInterface::class, [
                        'getPath' => '/calculator/calculate-modes',
                    ]),
                    'getQueryParams' => [
                        'firstNumber' => 500,
                        'secondNumber' => 700,
                    ],
                ]),
                'configure' => function (HTTPRouterInterface $router): void {
                    $router->addMiddleware(function ($request, $response, $next) {
                        throw new \Exception();
                    });

                    $router->get(
                        '/calculator/calculate-modes?{firstNumber=500}{?secondNumber=700}',
                        function ($num1, $num2) {
                            return "First number - {$num1}, Second number - {$num2}";
                        }
                    );
                },
                'expected' => "First number - 500, Second number - 700",
                'exception' => \Exception::class,
            ],
        ];
    }

    public static function addMiddlewareProvider(): array
    {
        return [
            'test where middleware is callable' => [
                'middleware' => fn() => 'callable',
            ],
            'test where middleware implements MiddlewareInterface' => [
                'middleware' => static::createStub(MiddlewareInterface::class)::class,
            ],
            'test catch error' => [
                'middleware' => 'Class',
                'expected' => "Class не соответствует интерфейсу - " . MiddlewareInterface::class,
            ],
        ];
    }
}
