<?php

namespace xamned\framework\cache\redis;

use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use Redis;
use xamned\framework\contracts\cache\CacheStorageInterface;

class RedisCache implements CacheStorageInterface
{
    public function __construct(
        private Redis $redis,
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->assertValidKey($key);

        $value = $this->redis->get($key);
        if ($value === false) {
            return $default;
        }

        return unserialize($value);
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $this->assertValidKey($key);

        $serialized = serialize($value);

        if ($ttl instanceof DateInterval) {
            $ttl = (new DateTimeImmutable())->add($ttl)->getTimestamp() - time();
        }

        if ($ttl !== null) {
            return $this->redis->setex($key, (int)$ttl, $serialized);
        }

        return $this->redis->set($key, $serialized);
    }

    public function delete(string $key): bool
    {
        $this->assertValidKey($key);

        return (bool)$this->redis->del($key);
    }

    public function clear(): bool
    {
        return $this->redis->flushDB();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keysArray = $this->assertValidKeys($keys);

        $values = [];
        $raw = $this->redis->mGet($keysArray);

        foreach ($keysArray as $i => $key) {
            $item = $raw[$i];
            if ($item === false || $item === null) {
                $values[$key] = $default;
            } else {
                $values[$key] = unserialize($item);
            }
        }

        return $values;
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        if ($ttl instanceof DateInterval) {
            $ttl = (new DateTimeImmutable())->add($ttl)->getTimestamp() - time();
        }

        $pipeline = $this->redis->multi(Redis::PIPELINE);
        foreach ($values as $key => $value) {
            $this->assertValidKey($key);

            $serialized = serialize($value);
            if ($ttl !== null) {
                $pipeline->setex($key, (int)$ttl, $serialized);
            } else {
                $pipeline->set($key, $serialized);
            }
        }
        $result = $pipeline->exec();

        return !in_array(false, $result, true);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $keysArray = $this->assertValidKeys($keys);

        return (bool)$this->redis->del(...$keysArray);
    }

    public function has(string $key): bool
    {
        $this->assertValidKey($key);

        return (bool)$this->redis->exists($key);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertValidKey(string $key): void
    {
        if (!is_string($key) || $key === '') {
            throw new InvalidArgumentException('Cache key must be a non-empty string.');
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertValidKeys(iterable $keys): array
    {
        $keysArray = [];
        foreach ($keys as $key) {
            $this->assertValidKey($key);
            $keysArray[] = $key;
        }

        return $keysArray;
    }
}