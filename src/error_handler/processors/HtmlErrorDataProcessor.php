<?php

namespace xamned\framework\error_handler\processors;

use xamned\framework\contracts\error_handler\ErrorDataProcessorInterface;
use xamned\framework\contracts\logger\DebugTagStorageInterface;

class HtmlErrorDataProcessor implements ErrorDataProcessorInterface
{
    public function __construct(
        private readonly DebugTagStorageInterface $debugTagStorage,
        private readonly string $envMode,
    ) {}

    public function process(\Throwable $error): array
    {
        return [
            'e' => $error,
            'envMode' => $this->envMode,
            'debugTag' => $this->debugTagStorage->getTag()
        ];
    }
}
