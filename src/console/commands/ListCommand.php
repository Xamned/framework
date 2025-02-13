<?php

namespace xamned\framework\console\commands;

use xamned\framework\contracts\console\ConsoleCommandInterface;
use xamned\framework\contracts\console\ConsoleInputInterface;
use xamned\framework\contracts\console\ConsoleKernelInterface;
use xamned\framework\contracts\console\ConsoleOutputInterface;

/**
 * Команда вывода информации о консольном ядре
 */
class ListCommand implements ConsoleCommandInterface
{
    private static string $signature = 'list {?commandName:имя команды|default=list}';

    private static string $description = 'Вывод информации о доступных командах';

    private bool $hidden = true;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleKernelInterface $kernel,
        private readonly ConsoleOutputInterface $output,
    )
    {
        $this->input->bindDefinitions($this);
    }

    public static function getSignature(): string
    {
        return static::$signature;
    }

    public static function getDescription(): string
    {
        return static::$description;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function execute(): void
    {
        $this->output->writeLn();
        $this->output->info($this->kernel->getAppName());
        $this->output->info(' ' . $this->kernel->getVersion());
        $this->output->writeLn(2);
        $this->output->warning("Фреймворк создан разработчиками {$this->kernel->getAppName()}.\n" 
            . "Является платформой для изучения базового поведения приложения созданного на PHP.\n" 
            . "Фреймворк не является production-ready реализацией и не предназначен для коммерческого использования."
        );
        $this->output->writeLn(2);

        $this->output->success('Доступные опции:');
        $this->output->writeLn();

        foreach ($this->input->getDefaultOptions() as $option => $optionDefinition) {
            $this->output->success("  --$option");
            $this->output->stdout(" - {$optionDefinition['description']}");
            $this->output->writeLn();
        }

        $this->output->writeLn();

        $this->output->success('Вызов:');
        $this->output->writeLn();
        $this->output->stdout('  команда [аргументы] [опции]');
        $this->output->writeLn(2);

        $this->output->stdout('Доступные команды:');
        $this->output->writeLn();

        foreach ($this->kernel->getCommands() as $commandName => $commandClass) {
            $this->output->success("  $commandName");
            $this->output->stdout(" - " . $commandClass::getDescription());
            $this->output->writeLn();
        }
        $this->output->writeLn();
    }
}
