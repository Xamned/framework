<?php

namespace xamned\framework\validator\rules;

use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\validator\exceptions\ValidationException;

class BooleanRule implements ValidationRuleInterface
{
    public function __construct(
        public readonly string $name = 'boolean',
        protected string $caseLine = 'булевым значением',
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function execute(mixed $value): void
    {
        if (is_bool($value) === false || $this->isBoolExpression($value) === false) {
            throw new ValidationException($this->caseLine);
        } 
    }

    private function isBoolExpression(mixed $value): bool
    {
        return match ($value) {
            1 => true,
            0 => true,
            '1' => true,
            '0' => true,
            default => false,
        };
    }
}
