<?php

namespace xamned\framework\contracts\validator;

interface TypeCastServiceInterface
{
    public function cast(mixed $value, string $type): mixed;
}
