<?php

namespace xamned\framework\contracts\validator;

interface ValidatorInterface
{
    public function validate(mixed $value): void;

    public function hasErrors(): bool;

    public function getErrors(): array;

    public function getPassedRules(): array;
}
