<?php

namespace xamned\framework\error_handler\processors;

use xamned\framework\contracts\error_handler\ErrorDataProcessorInterface;
use xamned\framework\contracts\logger\DebugTagStorageInterface;
use xamned\framework\ExecutionTypeEnum;

class JsonErrorDataProcessor implements ErrorDataProcessorInterface
{
    public function __construct(
        private readonly DebugTagStorageInterface $debugTagStorage,
        private readonly string $envMode,
    ) {}

    public function process(\Throwable $error): array
    {
        $data = [
            'message' => $error->getMessage(),
            'x-debug-tag' => $this->debugTagStorage->getTag(),
        ];

        if ($this->envMode === ExecutionTypeEnum::DEVELOPMENT->value) {
            $data['trace'] = $error->getTraceAsString();
        }

        return $data;
    }
}
