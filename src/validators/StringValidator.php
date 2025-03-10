<?php

namespace xamned\framework\validators;

class StringValidator extends AbstractValidator
{
    protected string $message = 'Значение "%s" не является строкой';

    public function validate(mixed $value): void
    {
        if (is_string($value) === false) {
            $this->fail();
        } 
    }
}
