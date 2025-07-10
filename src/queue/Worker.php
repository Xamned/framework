<?php

namespace xamned\framework\queue;

use Throwable;
use xamned\framework\contracts\event_dispatcher\EventDispatcherInterface;
use xamned\framework\contracts\logger\LoggerInterface;
use xamned\framework\contracts\queue\QueueInterface;
use xamned\framework\contracts\queue\WorkerInterface;
use xamned\framework\event_dispatcher\Message;
use xamned\framework\logger\enums\LogContext;

class Worker implements WorkerInterface
{
    private bool $active = true;

    public function __construct(
        private readonly QueueInterface $queue,
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        $this->eventDispatcher->trigger(LogContext::ATTACH->value, new Message('QUEUE'));
    }

    public function start(): void
    {
        $delay = 1;

        while ($this->active === true) {
            $result = $this->queue->pop();

            if ($result === null) {
                sleep($delay);
                $delay = min($delay * 2, 15);
                continue;
            }

            $delay = 1;

            try {
                $result->run();
                $this->logger->debug('Выполнена задача ' . $result::class);
            } catch (Throwable $e) {
                $this->logger->error('Задача ' . $result::class . ' закончилась с ошибкой: ' . $e->getMessage());
            }
        }
    }

    public function stop(): void
    {
        $this->active = false;
    }
}