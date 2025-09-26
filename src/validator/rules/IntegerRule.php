<?php

namespace xamned\framework\validator\rules;

use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\validator\exceptions\ValidationException;

class IntegerRule implements ValidationRuleInterface
{
    public function __construct(
        protected string $caseLine = 'должно быть целым числом'
    ) {}

    public function getName(): string
    {
        return 'integer';
    }

    public function execute(mixed $value): void
    {
        if (is_integer($value) === true || empty($value) === true) {
            return;
        }

        if (is_float($value) === true) {
            throw new ValidationException($this->caseLine);
        }

        if (is_numeric($value) === false) {
            throw new ValidationException($this->caseLine);
        }

        if (str_contains($value, '.') === true) {
            throw new ValidationException($this->caseLine);
        }
    }
}
