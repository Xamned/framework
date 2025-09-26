<?php

namespace xamned\framework\validator\rules;

use InvalidArgumentException;
use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\validator\exceptions\ValidationException;

class RegexRule implements ValidationRuleInterface
{
    public function __construct(
        protected string $caseLine = 'должно cоответствовать паттерну %s',
        protected string $pattern = '',
        protected bool $matchValue = false,
    ) {}

    public function getName(): string
    {
        return 'regex';
    }

    public function execute(mixed $value): void
    {
        if ($this->pattern === '') {
            throw new InvalidArgumentException('Должен быть указан pattern');
        }

        if (empty($value) === true) {
            return;
        }

        if ((bool) preg_match($this->pattern, $value) === $this->matchValue) {
            throw new ValidationException(sprintf($this->caseLine, $this->pattern));
        }
    }
}
