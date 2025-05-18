<?php

namespace xamned\framework\error_handler\processors;

use xamned\framework\contracts\error_handler\ErrorDataProcessorInterface;

class ConsoleErrorDataProcessor implements ErrorDataProcessorInterface
{
    public function process(\Throwable $error): array
    {
        return explode(PHP_EOL, (string) $error);
    }
}
