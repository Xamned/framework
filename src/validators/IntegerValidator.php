<?php

namespace xamned\framework\validators;

class IntegerValidator extends AbstractValidator
{
    protected string $message = 'Значение "%s" не является целым числом';

    public function validate(mixed $value): void
    {
        if (is_integer($value) === true) {
            return;
        }

        if (is_float($value) === true) {
            $this->fail();
        }

        if (is_numeric($value) === false) {
            $this->fail();
        }

        if (str_contains($value, '.') === true) {
            $this->fail();
        }
    }
}
