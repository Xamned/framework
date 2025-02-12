<?php

namespace framework\contracts\event_dispatcher;

use framework\event_dispatcher\Message;

interface ObserverInterface
{
    public function observe(Message $message): void;
}
