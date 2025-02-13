<?php

namespace xamned\framework\console\plugins;

use xamned\framework\console\ConsoleEvent;
use xamned\framework\contracts\console\ConsoleInputInterface;
use xamned\framework\contracts\console\ConsoleInputPluginInterface;
use xamned\framework\contracts\console\ConsoleOutputInterface;
use xamned\framework\contracts\event_dispatcher\EventDispatcherInterface;
use xamned\framework\contracts\event_dispatcher\ObserverInterface;
use xamned\framework\event_dispatcher\Message;

/**
 * Плагин перевода выполнения команды в фон
 */
class CommandDetachOptionPlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private string $optionName;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly EventDispatcherInterface $dispatcher,
    )
    {
        $this->optionName = 'detach';
    }

    public function init(): void
    {
        $this->input->addDefaultOption($this->optionName, 'Перевод выполнения команды в фоновый режим');
        $this->dispatcher->attach(ConsoleEvent::CONSOLE_INPUT_AFTER_PARSE->value, get_called_class());
    }

    public function observe(Message $message): void
    {
        if ($this->input->hasOption($this->optionName) === false) {
            return;
        }

        $this->output->detach();
    }
}