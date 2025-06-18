<?php

namespace xamned\framework\validator\rules;

use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\validator\exceptions\ValidationException;

class StringRule implements ValidationRuleInterface
{
    public function __construct(
        public readonly string $name = 'string',
        protected string $caseLine = 'должно быть строкой',
        protected ?int $min = null,
        protected string $minCaseLine = 'должно быть не меньше %s',
        protected ?int $max = null,
        protected string $maxCaseLine = 'должно быть не больше %s',
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function execute(mixed $value): void
    {
        if (empty($value) === true) {
            return;
        }

        if (is_string($value) === false) {
            throw new ValidationException($this->caseLine);
        }

        if ($this->min !== null && $this->min > mb_strlen($value)) {
            throw new ValidationException(sprintf($this->minCaseLine, $this->min));
        }

        if ($this->max !== null && $this->max < mb_strlen($value)) {
            throw new ValidationException(sprintf($this->maxCaseLine, $this->max));
        }
    }
}
