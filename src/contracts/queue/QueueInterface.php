<?php

namespace xamned\framework\contracts\queue;

interface QueueInterface
{
    public function push(JobInterface $job): void;

    public function pop(): ?JobInterface;

    public function isEmpty(): bool;
}