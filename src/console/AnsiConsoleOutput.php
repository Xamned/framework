<?php

namespace xamned\framework\console;

use xamned\framework\contracts\console\ConsoleKernelInterface;
use xamned\framework\contracts\console\ConsoleOutputInterface;
use xamned\framework\contracts\container\ContainerInterface;

/**
 * Обработка вывода в терминал консоли
 */
class AnsiConsoleOutput implements ConsoleOutputInterface
{    
    public function __construct(
    	private readonly ContainerInterface $container,
        private $stdOut = STDOUT,
        private $stdErr = STDERR,
    ) {}

    /**
     * Создать строку вывода в формате ANSI
     * 
     * @param  string $message сообщение вывода
     * @param  array $format формат вывода (цвет, стиль)
     * @return string
     */
    private function createAnsiLine(string $message, array $format = []): string
    {
        $code = implode(';', $format);

        return "\033[0m" . ($code !== '' ? "\033[" . $code . 'm' : '') . $message . "\033[0m";
    }

    public function stdout(string $message): void
    {
        $args = func_get_args();
        array_shift($args);

        $line = $this->createAnsiLine($message, $args);

        fwrite($this->stdOut, $line);
    }

    public function stdErr(string $message): void
    {
        $args = func_get_args();
        array_shift($args);

        $line = $this->createAnsiLine($message, $args);

        fwrite($this->stdErr, $line);
    }

    public function success(string $message): void
    {
        $this->stdout($message, ConsoleColors::FG_GREEN->value);
    }

    public function info(string $message): void
    {
        $this->stdout($message, ConsoleColors::FG_CYAN->value);
    }

    public function warning(string $message): void
    {
        $this->stdout($message, ConsoleColors::FG_YELLOW->value);
    }

    public function writeLn(int $count = 1): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->stdout("\n");
        }
    }

    public function setStdOut($resource): void
    {
        if (is_resource($this->stdOut) === true) {
            fclose($this->stdOut);
        }

        $this->stdOut = fopen($resource, 'ab');
    }

    public function setStdErr(string $resource): void
    {
        if (is_resource($this->stdErr) === true) {
            fclose($this->stdErr);
        }

        $this->stdErr = fopen($resource, 'ab');
    }

    public function detach($resource = '/dev/null'): void
    {
        $pid = pcntl_fork();

        $kernel = $this->container->get(ConsoleKernelInterface::class);

        if ($pid === -1) {
            $kernel->terminate(1);
        }

        if ((bool) $pid === true) {
            $kernel->terminate(0);
        }

        if (posix_setsid() === -1) {
            $kernel->terminate(1);
        }

        $this->setStdOut($resource);
    }
}
