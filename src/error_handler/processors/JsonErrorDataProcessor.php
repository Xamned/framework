<?php

namespace xamned\framework\error_handler\processors;

use Closure;
use xamned\framework\contracts\error_handler\ErrorDataProcessorInterface;
use xamned\framework\contracts\logger\DebugTagStorageInterface;
use xamned\framework\ExecutionTypeEnum;
use xamned\framework\http\exceptions\ForbiddenHttpException;
use xamned\framework\http\exceptions\HttpBadRequestException;
use xamned\framework\http\exceptions\HttpNotFoundException;
use xamned\framework\http\exceptions\HttpUnauthorizedException;

class JsonErrorDataProcessor implements ErrorDataProcessorInterface
{
    private Closure $modify;
    private array $typeMap = [
        HttpBadRequestException::class => 'BadRequest',
        HttpNotFoundException::class => 'NotFound',
        ForbiddenHttpException::class => 'Forbidden',
        HttpUnauthorizedException::class => 'Unauthorized',
    ];

    /**
     * @param \xamned\framework\contracts\logger\DebugTagStorageInterface $debugTagStorage
     * @param string $envMode
     * @param array $typeMap
     * @param Closure $modify function (array $data, \Throwable $error): array;
     */
    public function __construct(
        private readonly DebugTagStorageInterface $debugTagStorage,
        private readonly string $envMode,
        array $typeMap = [],
        ?Closure $modify = null,
    ) {
        $this->typeMap = array_merge($this->typeMap, $typeMap);

        if ($modify !== null) {
            $this->modify = $modify;
        }
    }

    public function process(\Throwable $error): array
    {
        $data = [
            'message' => $error->getMessage(),
            "type" => $this->getType($error),
            'x-debug-tag' => $this->debugTagStorage->getTag(),
        ];

        if ($this->envMode === ExecutionTypeEnum::DEVELOPMENT->value) {
            $data['trace'] = $error->getTraceAsString();
        }

        if (is_callable($this->modify) === true) {
            $data = $this->modify->call($this, $data, $error);
        }

        return $data;
    }

    private function getType(\Throwable $error): string
    {
        $type = $this->typeMap[$error::class] ?? null;

        if ($type !== null) {
            return $type;
        }

        if ($error instanceof \Exception) {
            return 'Exception';
        }

        if ($error instanceof \Error) {
            return 'Error';
        }

        return 'Throwable';
    }
}
