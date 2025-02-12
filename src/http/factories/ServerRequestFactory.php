<?php

namespace xamned\framework\http\factories;

use xamned\framework\http\ServerRequest;
use xamned\framework\contracts\container\ContainerInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;

final readonly class ServerRequestFactory implements ServerRequestFactoryInterface
{
    public function __construct(
        private ContainerInterface $container,
        private UriFactoryInterface    $uriFactory,
    ) {

    }

    /**
     * @inheritDoc
     */
    public function createServerRequest(
        string $method,
        $uri,
        array $serverParams = [],
        string $protocolVersion = '1.1',
        array $headers = [],
        StreamInterface $body = null,
        string $requestTarget = '',
        array $cookieParams = [],
        array $queryParams = [],
        array $uploadedFiles = [],
        array|null|object $parsedBody = null,
        array $attributes = [],
    ): ServerRequestInterface {
        if ($body === null) {
            $body = $this->container->get(StreamInterface::class);
        }

        return new ServerRequest(
            $protocolVersion,
            $headers,
            $body,
            $this->uriFactory->createUri($uri),
            $requestTarget,
            $method,
            $serverParams,
            $cookieParams,
            $queryParams,
            $uploadedFiles,
            $parsedBody,
            $attributes,
        );
    }
}