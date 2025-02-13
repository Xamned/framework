<?php

namespace xamned\framework\logger;

use DateTimeZone;
use xamned\framework\logger\enums\LogLevel;
use xamned\framework\contracts\logger\DebugTagStorageInterface;
use xamned\framework\contracts\logger\DebugTagGeneratorInterface;
use xamned\framework\contracts\logger\LogStateProcessorInterface;

class LogStateProcessor implements LogStateProcessorInterface
{
    public function __construct(
        private readonly string $index,
        private readonly LogContextStorage $contextStorage,
        private readonly DebugTagStorageInterface $debugTagStorage,
        private readonly DebugTagGeneratorInterface $debugTagGenerator,
        private readonly string $datetimeFormat = 'Y-m-d\TH:i:s.uP',
    ) {
    }

    /**
     * @return array
     */
    public function process(mixed $message)
    {
        $data = [
            'index' => $this->index,
            'category' => $message['category'] ?? null,
            'context' => empty($this->contextStorage->context) === false ? implode(':', $this->contextStorage->context) : null,
            'level' => $message['level'],
            'level_name' => strtolower(LogLevel::tryFrom($message['level'])->name),
            'action' => $_SERVER['REQUEST_URI'] ?? $_SERVER['argv'][0] . '/' . $_SERVER['argv'][1] ?? 'не определено',
            'action_type' => isset($_SERVER['REQUEST_URI']) === true ? 'web' : 'console',
            'datetime' => date_create()->format($this->datetimeFormat),
            'timestamp' => date_create('now', new DateTimeZone('UTC'))->format($this->datetimeFormat),
            'userId' => $message['userId'] ?? null,
            'ip' => $_SERVER['HTTP_X_REAL_IP'] ?? null,
            'real_ip' => isset($_SERVER['HTTP_X_FORWARDED_FOR']) === true ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : null,
            'x_debug_tag' => $this->debugTagStorage->getTag(),
            'message' => $message['message'],
            'exception' => $message['exception'] ?? [],
            'extras' => $message['extras'] ?? [],
        ];

        if ($message['message'] instanceof \Exception || $message['message'] instanceof \Error) {
            $data['message'] = $message['message']->getMessage();

            $data['exception'] = [
                'file' => $message['message']->getFile(),
                'line' => $message['message']->getLine(),
                'code' => $message['message']->getCode(),
                'trace' => explode(PHP_EOL, $message['message']->getTraceAsString()),
            ];
        }

        return $data;
    }
}