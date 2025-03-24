<?php

namespace xamned\framework\http\resource\responses;

class PatchResponse extends JsonResponse
{
    public function __construct(
        string $protocolVersion, 
        array $headers, 
        \Psr\Http\Message\StreamInterface $body, 
        int $statusCode = 200, 
        string $reasonPhrase = 'OK'
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
