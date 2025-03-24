<?php

namespace xamned\framework\http\resource\responses;

use xamned\framework\http\Response;

class JsonResponse extends Response
{
    public function __construct(
        string $protocolVersion, 
        array $headers, 
        \Psr\Http\Message\StreamInterface $body, 
        int $statusCode, 
        string $reasonPhrase
    ) {
        parent::__construct(
            $protocolVersion, 
            $headers, 
            $body, 
            $statusCode, 
            $reasonPhrase
        );

        $this->headers['Content-Type'] = 'application/json';
    }
}