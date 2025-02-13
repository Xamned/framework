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
 * Плагин для предоставления возможности вводить значения аргументов команды в интерактивном режиме
 */
class CommandInteractiveOptionPlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private string $optionName;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly EventDispatcherInterface $dispatcher,
    )
    {
        $this->optionName = 'interactive';
    }

    public function init(): void
    {
        $this->input->addDefaultOption($this->optionName, 'Предоставление возможности ' 
            . 'вводить значения аргументов команды в интерактивном режиме');
        $this->dispatcher->attach(ConsoleEvent::CONSOLE_INPUT_AFTER_PARSE->value, get_called_class());
    }

    public function observe(Message $message): void
    {
        if ($this->input->hasOption($this->optionName) === false) {
            return;
        }

        $definition = $this->input->getDefinition();

        foreach ($definition->getArguments() as $arg) {
            $argDefinition = $definition->getArgumentDefinition($arg);

            $this->output->success("Введите аргумент $arg");

            if ($argDefinition['description'] !== null) {
                $this->output->success(" ({$argDefinition['description']})");
            }

            if ($argDefinition['default'] !== null) {
                $this->output->success(" [{$argDefinition['default']}]");
            }

            $this->output->writeLn();

            $userInput = readline();

            $value = $userInput === '' 
                ? $argDefinition['default'] 
                : $userInput; 

            $this->input->setArgumentValue($arg, $value);
        }
    }
}