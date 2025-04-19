<?php

namespace xamned\framework\validator\rules;

use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\validator\exceptions\ValidationException;

class RequireRule implements ValidationRuleInterface
{
    public function __construct(
        public readonly string $name = 'require',
        protected string $caseLine = 'обязательным значением'
    ) {

    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function execute(mixed $value): void
    {
        if (is_null($value) === true) {
            throw new ValidationException($this->caseLine);
        }

        if (is_string($value) === true && trim($value) === '') {
            throw new ValidationException($this->caseLine);
        }

        if (is_array($value) === true && count($value) === 0) {
            throw new ValidationException($this->caseLine);
        }
    }
}