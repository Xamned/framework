<?php

namespace xamned\framework\error_handler\http;

use xamned\framework\contracts\ErrorHandlerInterface;
use xamned\framework\contracts\logger\DebugTagStorageInterface;
use xamned\framework\ExecutionTypeEnum;
use xamned\framework\http\MessageTypeEnum;
use xamned\framework\http\view\View;
use Throwable;

class HttpErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private readonly string $envMode,
        private readonly View $view,
        private readonly DebugTagStorageInterface $debugTagStorage,
        public string $responseFormat = MessageTypeEnum::HTML->value,
    ) {
    }


    /**
     * @inheritDoc
     */
    public function handle(Throwable $e): string
    {
        if ($this->isCompatibleWith(MessageTypeEnum::JSON->value) === true) {
            $jsonResponse = [
                'message' => $e->getMessage(),
                'x-debug-tag' => $this->debugTagStorage->getTag()
                ];

            if ($this->envMode === ExecutionTypeEnum::DEVELOPMENT->value) {
                $jsonResponse['trace'] = $e->getTraceAsString();
            }

            return json_encode($jsonResponse);
        }

        return $this->view->render(__DIR__ . '/views/error', [
            'e' => $e,
            'envMode' => $this->envMode,
            'debugTag' => $this->debugTagStorage->getTag()
        ]);
    }

    public function setResponseFormat(string $responseFormat): void
    {
        $this->responseFormat = $responseFormat;
    }

    public function isCompatibleWith(string $type): bool
    {
        return $this->responseFormat === $type;
    }
}
