<?php

namespace xamned\framework\contracts\validator;

interface ValidatorFactoryInterface
{
    public function create(array $ruleNames): ValidatorInterface;

    public function attachRule(string $name, array $ruleConfig): void;

    public function detachRule(string $name): void;

    public function attachRules(array $rules): void;

    public function detachRules(array $rules): void;

    public function setValidator(string $validator): void;
}
