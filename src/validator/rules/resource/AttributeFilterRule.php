<?php

namespace xamned\framework\validator\rules\resource;

use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\contracts\validator\ValidatorFactoryInterface;
use xamned\framework\validator\exceptions\ValidationException;

class AttributeFilterRule implements ValidationRuleInterface
{
    public function __construct(
        private readonly ValidatorFactoryInterface $validatorFactory,
        protected array $valueRule,
        protected array $validOperators,
        protected string $caseLine = 'должно использовать только следующие операторы: ',
    ) {}

    public function getName(): string
    {
        return 'attributeFilter';
    }

    public function execute(mixed $value): void
    {
        if (empty($value) === true) {
            return;
        }

        if (is_array($value) === false) {
            $this->validateValue($value);
            return;
        }

        foreach ($value as $operator => $realValue) {
            if (in_array($operator, $this->validOperators) === false) {
                throw new ValidationException($this->caseLine . implode(', ', $this->validOperators));
            }

            $this->validateValue($realValue);
        }
    }

    private function validateValue(mixed $value): void
    {
        $valueValidator = $this->validatorFactory->create([$this->valueRule]);

        $valueValidator->validate($value);

        if ($valueValidator->hasErrors() === true) {
            throw new ValidationException($valueValidator->getErrorsCasesLine());
        }
    }
}
