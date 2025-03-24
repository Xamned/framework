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
            'json_object' => json_decode($value),
            'json_array' => json_decode($value, true),
            default => $value,
        };
    }
}
