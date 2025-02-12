<?php

namespace framework\console;

use framework\contracts\console\ConsoleOutputInterface;
use framework\contracts\ErrorHandlerInterface;
use \Throwable;

class ErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private readonly ConsoleOutputInterface $output,
    ) {
    }

    public function handle(Throwable $e): string
    {
        foreach(explode(PHP_EOL, (string) $e) as $key => $row) {
            if ($key === 0) {
                $this->output->stdout(
                    PHP_EOL . PHP_EOL . "  $row" . PHP_EOL, 
                    ConsoleColors::BG_RED->value, ConsoleColors::FG_WHITE->value
                );
                $this->output->writeLn(2);
                continue;
            }

            if ($key === 1) {
                continue;
            }

            $this->output->stdout($row);
            $this->output->writeLn(2);
        }

        return (string) $e;
    }
}