<?php

namespace xamned\framework\validators;

class FloatValidator extends AbstractValidator
{
    protected string $message = 'Значение "%s" не является числом c плавающей точкой';

    public function validate(mixed $value): void
    {
        if (is_float($value) === true) {
            return;
        }

        if (is_numeric($value) === false) {
            $this->fail();
        }
    }
}
