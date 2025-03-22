<?php

namespace xamned\framework\contracts\http;

interface FormRequestInterface
{
    function rules(): array;

    function addRule(array $attributes, array|string $rule): void;

    function validate(): void;

    function addError(string $attribute, string $message): void;

    function getErrors(): array;

    function setSkipEmptyValues(): void;

    function getValues(): array;

    function load(array $data): void;
}
