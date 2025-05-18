<?php

namespace xamned\framework\contracts\error_handler;

use xamned\framework\error_handler\MessageTypeEnum;

interface ErrorDataProcessorFactoryInterface
{
    function create(MessageTypeEnum $type): ErrorDataProcessorInterface;

    function attach(MessageTypeEnum $type, string $processor, array $params = []): void;

    function detach(MessageTypeEnum $type): void;
}
