<?php

namespace xamned\framework\error_handler;

use InvalidArgumentException;
use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\error_handler\ErrorDataProcessorFactoryInterface;
use xamned\framework\contracts\error_handler\ErrorDataProcessorInterface;
use xamned\framework\error_handler\MessageTypeEnum;
use xamned\framework\error_handler\processors\ConsoleErrorDataProcessor;
use xamned\framework\error_handler\processors\HtmlErrorDataProcessor;
use xamned\framework\error_handler\processors\JsonErrorDataProcessor;

class ErrorDataProcessorFactory implements ErrorDataProcessorFactoryInterface
{
    private array $processors = [
        MessageTypeEnum::CONSOLE->value => ConsoleErrorDataProcessor::class,
        MessageTypeEnum::HTML->value => HtmlErrorDataProcessor::class,
        MessageTypeEnum::JSON->value => JsonErrorDataProcessor::class,
    ];
    private array $processorsParams = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly string $envMode,
        array $processors = [],
        array $processorsParams = [],
    ) {
        $this->processors = array_merge($this->processors, $processors);

        $this->processorsParams[MessageTypeEnum::HTML->value]['envMode'] = $this->envMode;
        $this->processorsParams[MessageTypeEnum::JSON->value]['envMode'] = $this->envMode;

        $this->processorsParams = array_merge_recursive($this->processorsParams, $processorsParams);
    }

    public function create(MessageTypeEnum $type): ErrorDataProcessorInterface
    {
        $processor = $this->processors[$type->value] ?? null;

        if ($processor === null) {
            throw new InvalidArgumentException("Не найден обработчик данных для типа сообщений - {$type->value}");
        }

        return $this->container->build($processor, $this->processorsParams[$type->value] ?? []);
    }

    public function attach(MessageTypeEnum $type, string $processor, array $params = []): void
    {
        if (is_subclass_of($processor, ErrorDataProcessorInterface::class) === false) {
            throw new InvalidArgumentException(
                "$processor не соответствует интерфейсу - " . ErrorDataProcessorInterface::class
            );
        }

        $this->processors[$type->value] = $processor;
        $this->processorsParams[$type->value] = $params;
    }

    public function detach(MessageTypeEnum $type): void
    {
        unset($this->processors[$type->value], $this->processorsParams[$type->value]);
    }
}
