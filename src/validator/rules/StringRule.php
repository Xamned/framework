<?php

namespace xamned\framework\validator\rules;

use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\validator\exceptions\ValidationException;

class StringRule implements ValidationRuleInterface
{
    public function __construct(
        public readonly string $name = 'string',
        protected string $message = 'строкой'
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function execute(mixed $value): void
    {
        if (is_string($value) === false) {
            throw new ValidationException($this->message);
        } 
    }
}
