<?php

namespace xamned\framework\contracts;

use \Throwable;

interface ErrorHandlerInterface
{
    /**
     * @param  Throwable $e объект ошибки
     * @return string
     */
    public function handle(Throwable $e): string;

    public function isCompatibleWith(string $type): bool;

    public function setResponseFormat(string $responseFormat): void;
}
