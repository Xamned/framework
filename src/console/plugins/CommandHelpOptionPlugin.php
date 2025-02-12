<?php

namespace xamned\framework\console\plugins;

use xamned\framework\console\CommandDefinition;
use xamned\framework\console\ConsoleEvent;
use xamned\framework\contracts\console\ConsoleInputInterface;
use xamned\framework\contracts\console\ConsoleInputPluginInterface;
use xamned\framework\contracts\console\ConsoleKernelInterface;
use xamned\framework\contracts\console\ConsoleOutputInterface;
use xamned\framework\contracts\event_dispatcher\EventDispatcherInterface;
use xamned\framework\contracts\event_dispatcher\ObserverInterface;
use xamned\framework\event_dispatcher\Message;

/**
 * Плагин вывода информации о команде
 */
class CommandHelpOptionPlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private string $optionName;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ConsoleKernelInterface $kernel,
    )
    {
        $this->optionName = 'help';
    }

    public function init(): void
    {
        $this->input->addDefaultOption($this->optionName, 'Вывод информации о команде');
        $this->dispatcher->attach(ConsoleEvent::CONSOLE_INPUT_AFTER_PARSE->value, get_called_class());
    }

    public function observe(Message $message): void
    {
        if ($this->input->hasOption($this->optionName) === false) {
            return;
        }

        $definition = $this->input->getDefinition();

        $this->output->writeLn();
        $this->output->success('Вызов:');
        $this->output->writeLn();

        $callMessage = $definition->getCommandName() . ' ';

        foreach ($definition->getArguments() as $arg) {
            $callMessage .= "[$arg] ";
        }

        $callMessage .= '[опции]';
        $this->output->stdout("  $callMessage");
        $this->output->writeLn(2);

        $this->output->info('Назначение:');
        $this->output->writeLn();
        $this->output->stdout('  ' . $definition->getCommandDescription());
        $this->output->writeLn(2);

        if ($definition->getArguments() !== []) {
            $this->output->info('Аргументы:');
            $this->output->writeLn();
        }

        foreach ($definition->getArguments() as $arg) {
            $argDefinition = $definition->getArgumentDefinition($arg);

            $this->output->success("  $arg");
            $this->output->stdout(" {$argDefinition['description']}, ");
            $this->output->stdout($argDefinition['required'] === true 
                ? 'обязательный параметр' 
                : 'не обязательный параметр'
            );

            if ($argDefinition['default'] !== null) {
                $this->output->stdout(", значение по умолчанию: {$argDefinition['default']}");
            }

            $this->output->writeLn();
        }

        if ($definition->getArguments() !== []) {
            $this->output->writeLn();
        }

        if ($definition->getOptions() !== []) {
            $this->output->info('Опции:');
            $this->output->writeLn();

            foreach ($definition->getOptions() as $option) {
                $optionDefinition = $definition->getOptionDefinition($option);
    
                $this->output->success("  $option");
                $this->output->stdout(" {$optionDefinition['description']}");
                $this->output->writeLn();
            }

            $this->output->writeLn();
        }
       
        $this->kernel->terminate(1);
    }
}
