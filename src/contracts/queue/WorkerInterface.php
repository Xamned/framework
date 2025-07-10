<?php

namespace xamned\framework\contracts\queue;

interface WorkerInterface
{
    public function start(): void;

    public function stop(): void;
}