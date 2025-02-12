<?php

namespace framework\logger;

use framework\contracts\logger\LogStateProcessorInterface;

/**
 * Логгер в поток вывода докер-контейнера
 */
class StdoutLogger extends AbstractLogger
{
    public function __construct(
        private readonly LogStateProcessorInterface $logStateProcessor,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    protected function formatMessage(string $level, mixed $message): string
    {
        $normalMessage['level'] = $level;

        $normalMessage['message'] = $message;

        $value = $this->logStateProcessor->process($normalMessage);

        return json_encode($value, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }

    /**
     * {@inheritdoc}
     */
    protected function writeLog(string $log): void
    {
        $stdout = fopen('php://stdout', 'w');

        fwrite($stdout, $log);

        fclose($stdout);
    }
}
