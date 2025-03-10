<?php

namespace xamned\framework\validators;

class BooleanValidator extends AbstractValidator
{
    protected string $message = 'Значение "%s" не является булевым значением';

    public function validate(mixed $value): void
    {
        if (is_bool($value) === false || $this->isBoolExpression($value) === false) {
            $this->fail();
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
