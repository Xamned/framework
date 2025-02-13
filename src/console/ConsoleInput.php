<?php

namespace xamned\framework\console;

use xamned\framework\contracts\console\ConsoleCommandInterface;
use xamned\framework\contracts\console\ConsoleInputInterface;
use xamned\framework\contracts\console\ConsoleInputPluginInterface;
use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\event_dispatcher\EventDispatcherInterface;
use xamned\framework\event_dispatcher\Message;

/**
 * Обработка ввода в терминал консоли
 */
class ConsoleInput implements ConsoleInputInterface
{
    /**
     * @var array аргументы введенные в консоль
     */
    private array $tokens = [];

    /**
     * @var array аргументы, переданные как аргументы вызова в консоль
     */
    private array $arguments = [];

    /**
     * @var array опции, доступные для каждой команды по умолчанию
     */
    private array $defaultOptions = [];

    /**
     * @var array опции переданные как аргументы вызова в консоль
     */
    private array $options = [];

    /**
     * @var CommandDefinition объект описания консольного вызова
     */
    private CommandDefinition $definition;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly EventDispatcherInterface $dispatcher
    ) {
        $argv ??= $_SERVER['argv'] ?? [];

        array_shift($argv);

        $this->tokens = $argv;
    }

    public function getDefinition(): CommandDefinition
    {
        return $this->definition;
    }

    public function addPlugins(array $plugins): void
    {
        foreach ($plugins as $plugin) {
            if (is_subclass_of($plugin, ConsoleInputPluginInterface::class) === false) {
                throw new \RuntimeException("$plugin не соответствует интерфейсу" . ConsoleInputPluginInterface::class);
            }

            $this->container->build($plugin)->init();
        }
    }

    public function getFirstArgument(): string|null
    {
        return $this->tokens[0] ?? null;
    }

    /**
     * Преобразование введенных аргументов в консоль в аргументы и опции вызова команды
     * 
     * @return void
     */
    private function parse(): void
    {
        $lastToken = null;
        foreach ($this->tokens as $key => $token) {
            if ($key === 0) {
                continue;
            }

            if (str_starts_with($token, '--') === true) {
                $this->parseOption($token);
                $lastToken = $token;
                continue;
            }

            if ($lastToken !== null) {
                $this->setOptionValue($option = substr($lastToken, 2), $token);
                $lastToken = null;
                continue;
            }

            $this->parseArgument($token);
        }
    }

    /**
     * Валидация аргументов, переданных для вызова команды
     * 
     * @return void
     */
    private function validate(): void
    {
        foreach ($this->definition->getArguments() as $arg) {
            if (isset($this->arguments[$arg]) === false && $this->definition->isRequired($arg) === true) {
                throw new \InvalidArgumentException("Не указан обязательный параметр $arg");
            }
        }
    }

    /**
     * Установка значений по умолчанию для аргументов вызова команды,
     * имеющих значения по умолчанию
     * 
     * @return void
     */
    private function setDefaults(): void
    {
        foreach ($this->definition->getArguments() as $arg) {
            if (isset($this->arguments[$arg]) === false && $this->definition->isRequired($arg) === false) {
                $this->arguments[$arg] = $this->definition->getDefaultValue($arg);
            }
        }
    }

    public function bindDefinitions(ConsoleCommandInterface $command): void
    {
        $this->arguments = [];
        $this->options = array_fill_keys(array_keys($this->defaultOptions), false);

        $this->dispatcher->trigger(ConsoleEvent::CONSOLE_INPUT_BEFORE_PARSE->value, new Message($this));

        $this->definition = new CommandDefinition($command::getSignature(), $command::getDescription());

        $this->parse();

        $this->dispatcher->trigger(ConsoleEvent::CONSOLE_INPUT_AFTER_PARSE->value, new Message($this));

        $this->validate();
        $this->setDefaults();
    }

    /**
     * Регистрация вызванной опции
     * 
     * @param string $option имя опции
     * @return void
     */
    private function parseOption(string $option): void
    {
        $option = substr($option, 2);

        $options = array_merge(array_keys($this->options), $this->definition->getOptions());

        if (in_array($option, $options) === false) {

            throw new \InvalidArgumentException(sprintf('Опция "--%s" не существует', $option));
        }

        $this->options[$option] = true;
    }

    public function setArgumentValue(string $name, null|string $value): void
    {
        $this->arguments[$name] = is_numeric($value) ? (int) $value : $value;
    }

    public function setOptionValue(string $name, null|string $value): void
    {
        $this->options[$name] = is_numeric($value) ? (int) $value : $value;
    }

    /**
     * Регистрация вызванного аргумента
     * 
     * @param string $arg введенный аргумент
     * @return void
     */
    private function parseArgument(string $arg): void
    {
        foreach ($this->definition->getArguments() as $name) {

            if (isset($this->arguments[$name]) === true) {
                continue;
            }

            $this->setArgumentValue($name, $arg);
            return;
        }

        throw new \RuntimeException('Слишком много аргументов. Ожидается аргументов: ' . count($this->arguments));
    }

    public function hasArgument(string $name): bool
    {
        return isset($this->arguments[$name]);
    }

    public function getArgument(string $name): int|string
    {
        if (array_key_exists($name, $this->arguments) === false) {
            throw new \InvalidArgumentException(sprintf('Аргумент "%s" не существует', $name));
        }

        return $this->arguments[$name];
    }

    public function getOption(string $name): int|string
    {
        if (array_key_exists($name, $this->options) === false) {
            throw new \InvalidArgumentException(sprintf('Опция "%s" не существует', $name));
        }

        return $this->options[$name];
    }

    public function addDefaultOption(string $name, string $description): void
    {
        $this->defaultOptions[$name] = [
            'description' => $description,
        ];
    }

    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options) && (bool) $this->options[$name] === true;
    }

    public function getDefaultOptions(): array
    {
        return $this->defaultOptions;
    }

    public function enableOption(string $name): void
    {
        $this->options[$name] = true;
    }

    public function getTokens()
    {
        return $this->tokens;
    }
}
