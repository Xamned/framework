<?php

namespace xamned\framework\validator\rules;

use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\validator\exceptions\ValidationException;

class FloatRule implements ValidationRuleInterface
{
    public function __construct(
        protected string $caseLine = 'должно быть числом c плавающей точкой'
    ) {}

    public function getName(): string
    {
        return 'float';
    }

    public function execute(mixed $value): void
    {
        if (is_float($value) === true || empty($value) === true) {
            return;
        }

        if (is_numeric($value) === false) {
            throw new ValidationException($this->caseLine);
        }
    }
}
