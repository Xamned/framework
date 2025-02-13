<?php

namespace xamned\framework\contracts\logger;

interface LogStateProcessorInterface
{
    public function process(mixed $message);
}