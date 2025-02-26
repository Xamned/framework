<?php

namespace xamned\framework\contracts\http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpKernelInterface
{
    /**
     * Обработка входящего запроса
     *
     * @return ResponseInterface объект ответа
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;
}