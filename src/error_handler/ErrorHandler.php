<?php

namespace xamned\framework\error_handler;

use Throwable;
use xamned\framework\contracts\error_handler\ErrorHandlerInterface;
use xamned\framework\contracts\error_handler\ErrorDataProcessorFactoryInterface;
use xamned\framework\contracts\error_handler\ErrorRendererFactoryInterface;
use xamned\framework\error_handler\MessageTypeEnum;

class ErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private readonly ErrorRendererFactoryInterface $errorRendererFactory,
        private readonly ErrorDataProcessorFactoryInterface $errorDataProcessorFactory,
        private MessageTypeEnum $responseFormat = MessageTypeEnum::HTML,
    ) {}

    public function handle(Throwable $e): string
    {
        $processor = $this->errorDataProcessorFactory->create($this->responseFormat);
        $renderer = $this->errorRendererFactory->create($this->responseFormat);

        return $renderer->render($processor->process($e));
    }

    public function setResponseFormat(MessageTypeEnum $responseFormat): void
    {
        $this->responseFormat = $responseFormat;
    }
}
