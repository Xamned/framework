<?php

namespace framework\logger;

class LogContextStorage
{
    public array $context;

    public function attachContext($value): void
    {
        $this->context[$value] = $value;
    }

    public function detachContext($value): void
    {
        if (isset($this->context[$value]) === true) {
            unset($this->context[$value]);
        }
    }

    public function cleanseContext(): void
    {
        $this->context = [];
    }
}