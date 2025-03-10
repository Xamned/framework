<?php

namespace xamned\framework\validators;

use xamned\framework\contracts\validators\ValidatorInterface;
use xamned\framework\validators\exceptions\ValidationFailedException;

abstract class AbstractValidator implements ValidatorInterface
{
    protected string $message = 'Значение "%s" не прошло валидацию';
    protected string $attribute = '';

    public function __construct(array $config = []) 
    {
        foreach ($config as $property => $value) {
            $this->$property = $value;
        }
    }

    abstract public function validate(mixed $value): void;

    /**
     * @throws ValidationFailedException
     * @return never
     */
    protected function fail(): never
    {
        throw new ValidationFailedException(sprintf($this->message, $this->attribute));
    }
}
