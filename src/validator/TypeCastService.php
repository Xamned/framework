<?php

namespace xamned\framework\validator;

use xamned\framework\contracts\validator\TypeCastServiceInterface;

class TypeCastService implements TypeCastServiceInterface
{
    public function cast(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'int' => (int) $value,
            'float' => (float) $value,
            'real' => (float) $value,
            'double' => (float) $value,
            'binary' => (string) $value,
            'string' => (string) $value,
            'boolean' => (bool) $value,
            'bool' => (bool) $value,
            'array' => (array) $value,
            'object' => (object) $value,
            default => $value,
        };
    }
}
