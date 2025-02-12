<?php

namespace framework\http\factories;

use framework\http\Response;
use framework\contracts\container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

final readonly class ResponseFactory implements ResponseFactoryInterface
{
    public function __construct(
        private ContainerInterface $container
    ) {

    }

    /**
     * @inheritDoc
     */
    public function createResponse(
        int $code = 200,
        string $reasonPhrase = '',
        string $protocolVersion = '1.1',
        array $headers = [],
        StreamInterface $body = null
    ): ResponseInterface {
        if ($body === null) {
            $body = $this->container->get(StreamInterface::class);
        }

        return new Response(
            $protocolVersion,
            $headers,
            $body,
            $code,
            $reasonPhrase
        );
    }
}