<?php

namespace xamned\framework\contracts\validators;

interface ValidatorFactoryInterface
{
    public function create(string $name, array $config = []): ValidatorInterface;

    public function registerValidator(string $name, array $validatorConfig): void;

    public function unregisterValidator($name): void;

    public function registerValidators(array $validators): void;

    public function unregisterValidators(array $validators): void;
}
