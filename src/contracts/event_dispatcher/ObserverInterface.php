<?php

namespace xamned\framework\contracts\event_dispatcher;

use xamned\framework\event_dispatcher\Message;

interface ObserverInterface
{
    public function observe(Message $message): void;
}
