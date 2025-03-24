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
}
