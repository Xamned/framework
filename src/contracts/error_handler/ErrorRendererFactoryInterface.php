<?php

namespace xamned\framework\contracts\error_handler;

use xamned\framework\error_handler\MessageTypeEnum;

interface ErrorRendererFactoryInterface
{
    function create(MessageTypeEnum $type): ErrorRendererInterface;

    function attach(MessageTypeEnum $type, string $renderer): void;

    function detach(MessageTypeEnum $type): void;
}
