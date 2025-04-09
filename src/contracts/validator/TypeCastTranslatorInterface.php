<?php

namespace xamned\framework\contracts\validator;

interface TypeCastTranslatorInterface
{
    public function translate(mixed $value, string $type): mixed;
}
