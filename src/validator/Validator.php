<?php

namespace xamned\framework\validator;

use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\contracts\validator\ValidatorInterface;
use xamned\framework\validator\exceptions\ValidationException;

class Validator implements ValidatorInterface
{
    /** @var ValidationException[] */
    protected array $errors = [];
    /** @var string[] */
    protected array $passedRules = [];

    public function __construct(
        protected array $rules
    ) {
    }

    public function validate(mixed $value): void
    {
        /** @var $rule ValidationRuleInterface */
        foreach ($this->rules as $rule) {
            try {
                $rule->execute($value);
                
                $this->passedRules[] = $rule->getName();
            } catch (ValidationException $e) {
                $this->errors[] = $e;
            }
        }
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /** @return ValidationException[] */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getPassedRules(): array
    {
        return $this->passedRules;
    }

    public function getErrorsCasesLine(): string
    {
        $caseLine = array_map(fn(ValidationException $e): string => $e->getMessage(), $this->errors);

        return implode(', ', $caseLine);
    }
}
