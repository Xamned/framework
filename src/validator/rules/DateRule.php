<?php

namespace app\components\validator\rules;

use DateTime;
use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\validator\exceptions\ValidationException;

final class DateRule implements ValidationRuleInterface
{
    public function __construct(
        public string $format = DateTime::RFC3339_EXTENDED,
        public string $caseLine = 'должно быть формата %s'
    ) {}

    public function getName(): string
    {
        return 'date';
    }

    public function execute(mixed $value): void
    {
        if (empty($value) === true) {
            return;
        }
        
        if (DateTime::createFromFormat($this->format, $value) === false) {
            throw new ValidationException(sprintf($this->caseLine, $this->format));
        }
    }
}
