<?php

namespace xamned\framework\error_handler\renderers;

use Psr\Http\Message\ResponseInterface;
use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\error_handler\ErrorRendererInterface;

class JsonErrorRenderer implements ErrorRendererInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    public function render(array $data): string
    {
        /** @var ResponseInterface */
        $response = $this->container->get(ResponseInterface::class);
        $response = $response->withHeader('Content-Type', 'application/json');

        $this->container->attach(ResponseInterface::class, function() use ($response): ResponseInterface {
            return $response;
        });

        return json_encode($data);
    }
}
