<?php

namespace framework\logger;

use framework\contracts\logger\DebugTagStorageInterface;

class DebugTagStorage implements DebugTagStorageInterface
{
    /**
     * Cтрока значения тега отладки
     * 
     * @var string|null
     */
    private string|null $tag = null;

    /**
     * {@inheritdoc}
     */
    public function getTag(): string
    {
        if ($this->tag === null) {
            throw new \RuntimeException('Тег отладки не определен');
        }

        return $this->tag;
    }

    /**
     * {@inheritdoc}
     */
    public function setTag(string $tag): void
    {
        $this->tag = $tag;
    }

    /**
     * {@inheritdoc}
     */
    public function issetTag(): bool
    {
        return $this->tag !== null;
    }
}
