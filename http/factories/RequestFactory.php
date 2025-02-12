<?php

namespace framework\http\factories;

use framework\http\Request;
use framework\contracts\container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;

final readonly class RequestFactory implements RequestFactoryInterface
{
    public function __construct(
        private ContainerInterface $container,
        private UriFactoryInterface $uriFactory,
    ) {

    }


    /**
     * @inheritDoc
     */
    public function createRequest(
        string $method,
        $uri,
        array $headers = [],
        StreamInterface $body = null,
        string $protocolVersion = '1.1',
        string $requestTarget = ''
    ): RequestInterface {
        if ($body === null) {
            $body = $this->container->get(StreamInterface::class);
        }

        return new Request(
            $protocolVersion,
            $headers,
            $body,
            $this->uriFactory->createUri($uri),
            $requestTarget,
            $method,
        );
    }
}