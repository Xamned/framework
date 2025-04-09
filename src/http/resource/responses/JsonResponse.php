<?php

namespace xamned\framework\http\resource\responses;

readonly class JsonResponse
{
    public function __construct(public array $data) {}
}