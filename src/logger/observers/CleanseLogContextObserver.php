<?php

namespace xamned\framework\logger\observers;

use xamned\framework\contracts\event_dispatcher\ObserverInterface;
use xamned\framework\event_dispatcher\Message;
use xamned\framework\logger\LogContextStorage;

class CleanseLogContextObserver implements ObserverInterface
{
    public function __construct(
        private LogContextStorage $logContextStorage,
    ) {
    }

    public function observe(Message $message): void
    {
        $this->logContextStorage->cleanseContext();
    }
}
