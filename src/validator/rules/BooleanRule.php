<?php

namespace xamned\framework\validator\rules;

use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\validator\exceptions\ValidationException;

class BooleanRule implements ValidationRuleInterface
{
    public function __construct(
        protected string $caseLine = 'должно быть булевым значением',
    ) {}

    public function getName(): string
    {
        return 'boolean';
    }

    public function execute(mixed $value): void
    {
        if (empty($value) === true) {
            return;
        }

        if (is_bool($value) === false || $this->isBoolExpression($value) === false) {
            throw new ValidationException($this->caseLine);
        } 
    }

    private function isBoolExpression(mixed $value): bool
    {
        return match ($value) {
            1, 0, '1', '0' => true,
            default => false,
        };
    }
}
