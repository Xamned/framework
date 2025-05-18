<?php

namespace xamned\framework\contracts\error_handler;

use \Throwable;
use xamned\framework\error_handler\MessageTypeEnum;

interface ErrorHandlerInterface
{
    /**
     * @param Throwable $e объект ошибки
     * @return string
     */
    public function handle(Throwable $e): string;

    public function setResponseFormat(MessageTypeEnum $responseFormat): void;
}
