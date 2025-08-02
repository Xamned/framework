<?php

namespace xamned\framework\db\mysql;

final readonly class StatementParameters
{
    public function __construct(
        public string $sql,
        public array $bindings
    ) {}
}
