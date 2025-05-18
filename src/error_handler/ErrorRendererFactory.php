<?php

namespace xamned\framework\error_handler;

use InvalidArgumentException;
use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\error_handler\ErrorRendererInterface;
use xamned\framework\error_handler\MessageTypeEnum;
use xamned\framework\contracts\error_handler\ErrorRendererFactoryInterface;
use xamned\framework\error_handler\renderers\ConsoleErrorRenderer;
use xamned\framework\error_handler\renderers\HtmlErrorRenderer;
use xamned\framework\error_handler\renderers\JsonErrorRenderer;

class ErrorRendererFactory implements ErrorRendererFactoryInterface
{
    private array $renderers = [
        MessageTypeEnum::CONSOLE->value => ConsoleErrorRenderer::class,
        MessageTypeEnum::HTML->value => HtmlErrorRenderer::class,
        MessageTypeEnum::JSON->value => JsonErrorRenderer::class,
    ];

    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    public function create(MessageTypeEnum $type): ErrorRendererInterface
    {
        if (isset($this->renderers[$type->value]) === false) {
            throw new InvalidArgumentException("Не найден обработчик отображения для типа сообщений - {$type->value}");
        }

        return $this->container->build($this->renderers[$type->value]);
    }

    public function attach(MessageTypeEnum $type, string $renderer): void
    {
        if (is_subclass_of($renderer, ErrorRendererInterface::class) === false) {
            throw new InvalidArgumentException(
                "$renderer не соответствует интерфейсу - " . ErrorRendererInterface::class
            );
        }

        $this->renderers[$type->value] = $renderer;
    }

    public function detach(MessageTypeEnum $type): void
    {
        unset($this->renderers[$type->value]);
    }
}
