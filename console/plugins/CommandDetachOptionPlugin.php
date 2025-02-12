<?php

namespace framework\console\plugins;

use framework\console\ConsoleEvent;
use framework\contracts\console\ConsoleInputInterface;
use framework\contracts\console\ConsoleInputPluginInterface;
use framework\contracts\console\ConsoleOutputInterface;
use framework\contracts\event_dispatcher\EventDispatcherInterface;
use framework\contracts\event_dispatcher\ObserverInterface;
use framework\event_dispatcher\Message;

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