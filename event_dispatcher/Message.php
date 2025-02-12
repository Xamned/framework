<?php

namespace framework\event_dispatcher;

class Message
{
    public function __construct(
        public readonly mixed $message,
    ) {   
    }
}
