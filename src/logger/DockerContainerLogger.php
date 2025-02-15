<?php

namespace xamned\framework\logger;

use xamned\framework\contracts\logger\LogStateProcessorInterface;

/**
 * Логгер в поток вывода докер-контейнера
 */
class DockerContainerLogger extends AbstractLogger
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

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * {@inheritdoc}
     */
    protected function writeLog(string $log): void
    {
        exec("echo \"$log\" >>/proc/1/fd/2");
    }
}
