<?php

namespace xamned\framework\http\resource\responses;

class CreateResponse extends JsonResponse
{
    public function __construct(
        string $protocolVersion, 
        array $headers, 
        \Psr\Http\Message\StreamInterface $body, 
        int $statusCode = 201, 
        string $reasonPhrase = 'Created'
    ) {
        parent::__construct(
            $protocolVersion, 
            $headers, 
            $body, 
            $statusCode, 
            $reasonPhrase
        );
    }
}
