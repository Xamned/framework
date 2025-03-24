<?php

namespace xamned\framework\validator\rules;

use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\validator\exceptions\ValidationException;

class FloatRule implements ValidationRuleInterface
{
    public function __construct(
        public readonly string $name = 'float',
        protected string $caseLine = 'числом c плавающей точкой'
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function execute(mixed $value): void
    {
        if (is_float($value) === true) {
            return;
        }

        if (is_numeric($value) === false) {
            throw new ValidationException($this->caseLine);
        }
    }
}
