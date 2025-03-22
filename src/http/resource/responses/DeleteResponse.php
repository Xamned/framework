<?php

namespace xamned\framework\http\resource\responses;

class DeleteResponse extends JsonResponse
{
    public function __construct(
        string $protocolVersion, 
        array $headers, 
        \Psr\Http\Message\StreamInterface $body, 
        int $statusCode = 204, 
        string $reasonPhrase = 'No content'
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
