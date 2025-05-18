<?php

namespace xamned\framework\error_handler\renderers;

use xamned\framework\console\AnsiLineFormater;
use xamned\framework\console\ConsoleColors;
use xamned\framework\contracts\error_handler\ErrorRendererInterface;

class ConsoleErrorRenderer implements ErrorRendererInterface
{
    public function __construct(
        private readonly AnsiLineFormater $lineFormater,
    ) {}

    public function render(array $data): string
    {
        $message = ' ';

        foreach($data as $key => $row) {
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
