<?php

namespace xamned\framework\console\commands;

use xamned\framework\contracts\console\ConsoleCommandInterface;
use xamned\framework\contracts\queue\WorkerInterface;

class QueueCommand implements ConsoleCommandInterface
{
    private static string $signature = 'queue-start';

    private static string $description = 'Команда запуска очереди';

    public function __construct(
        private readonly WorkerInterface $worker,
    ) {
    }

    public static function getDescription(): string
    {
        return static::$description;
    }

    public static function getSignature(): string
    {
        return static::$signature;
    }

    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        $this->worker->start();
    }
}