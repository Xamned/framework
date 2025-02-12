<?php

namespace xamned\framework\contracts\logger;

interface DebugTagStorageInterface
{
    /**
     * Получить значение тега
     * 
     * @return string
     */
    public function getTag(): string;

    /**
     * Установить значение тега
     * 
     * @param string $tag
     * @return void
     */
    public function setTag(string $tag): void;

    /**
     * Проверить наличие значение тега
     * 
     * @return bool
     */
    public function issetTag(): bool;
}