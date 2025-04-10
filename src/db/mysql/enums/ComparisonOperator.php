<?php

namespace xamned\framework\db\mysql\enums;

enum ComparisonOperator: string
{
    case EQUAL = '=';
    case NOT_EQUAL = '!=';
    case GREATER = '>';
    case LESS = '<';
    case GREATER_OR_EQUAL = '>=';
    case LESS_OR_EQUAL = '<=';

    public function compare($left, $right): bool
    {
        return match($this) {
            self::EQUAL => $left === $right,
            self::NOT_EQUAL => $left !== $right,
            self::GREATER => $left > $right,
            self::LESS => $left < $right,
            self::GREATER_OR_EQUAL => $left >= $right,
            self::LESS_OR_EQUAL => $left <= $right,
        };
    }
}
