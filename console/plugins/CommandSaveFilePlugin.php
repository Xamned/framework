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
 * Плагин перевода записи вывода результата выполнения команды в файл
 */
class CommandSaveFilePlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private string $optionName;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly EventDispatcherInterface $dispatcher,
    )
    {
        $this->optionName = 'save-file';
    }

    public function init(): void
    {
        $this->input->addDefaultOption($this->optionName, 'Перевод записи вывода результата выполнения команды в файл');
        $this->dispatcher->attach(ConsoleEvent::CONSOLE_INPUT_AFTER_PARSE->value, get_called_class());
    }

    public function observe(Message $message): void
    {
        if ($this->input->hasOption($this->optionName) === false) {
            return;
        }

        $filename = $this->input->getOption($this->optionName);

        if (file_exists($filename) === false) {
            mkdir(dirname($filename), 0777, true);
        }
        
        $this->output->setStdOut($filename);
    }
}