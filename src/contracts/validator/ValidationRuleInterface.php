<?php

namespace xamned\framework\contracts\validator;

use xamned\framework\validator\exceptions\ValidationException;

interface ValidationRuleInterface
{
    public function getName(): string;

    /**
     * @throws ValidationException
     * @return void
     */
    public function execute(mixed $value): void;
}
