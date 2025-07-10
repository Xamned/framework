<?php

namespace xamned\framework\queue\redis;

use Redis;
use xamned\framework\contracts\queue\JobInterface;
use xamned\framework\contracts\queue\QueueInterface;

class RedisQueue implements QueueInterface
{
    public function __construct(
        private readonly Redis $redis,
        private string $key,
    ) {
    }

    public function push(JobInterface $job): void
    {
        $payload = serialize($job);
        $this->redis->rPush($this->key, $payload);
    }

    public function pop(): ?JobInterface
    {
        $payload = $this->redis->lPop($this->key);

        if (empty($payload) === true) {
            return null;
        }

        return unserialize($payload);
    }

    public function isEmpty(): bool
    {
        return $this->redis->lLen($this->key) === 0;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }
}