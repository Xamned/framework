<?php

namespace xamned\framework\console;

use xamned\framework\contracts\ErrorHandlerInterface;
use \Throwable;

class ErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private readonly AnsiLineFormater $lineFormater,
    ) {
    }

    public function handle(Throwable $e): string
    {
        $message = ' ';

        foreach(explode(PHP_EOL, (string) $e) as $key => $row) {
            if ($key === 0) {
                $message .= $this->lineFormater->format(
                    PHP_EOL . PHP_EOL . "  $row" . PHP_EOL,
                    [ConsoleColors::BG_RED->value, ConsoleColors::FG_WHITE->value]
                );

                $message .= PHP_EOL . PHP_EOL;
                continue;
            }

            if ($key === 1) {
                continue;
            }

            $message .= $this->lineFormater->format($row);
            $message .= PHP_EOL . PHP_EOL;
        }

        return $message;
    }
}