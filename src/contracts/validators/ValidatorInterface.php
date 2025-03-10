<?php

namespace xamned\framework\contracts\validators;

use xamned\framework\validators\exceptions\ValidationFailedException;

interface ValidatorInterface
{
    /**
     * @throws ValidationFailedException
     * @return void
     */
    public function validate(mixed $value): void;
}
