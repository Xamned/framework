<?php

namespace xamned\framework\validator\rules;

use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\validator\exceptions\ValidationException;

class IntegerRule implements ValidationRuleInterface
{
    public function __construct(
        public readonly string $name = 'integer',
        protected string $message = 'целым числом'
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function execute(mixed $value): void
    {
        if (is_integer($value) === true) {
            return;
        }

        if (is_float($value) === true) {
            throw new ValidationException($this->message);
        }

        if (is_numeric($value) === false) {
            throw new ValidationException($this->message);
        }

        if (str_contains($value, '.') === true) {
            throw new ValidationException($this->message);
        }
    }
}
