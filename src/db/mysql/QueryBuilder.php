<?php

namespace xamned\framework\db\mysql;

use xamned\framework\contracts\db\MysqlQueryBuilderInterface;

class QueryBuilder implements MysqlQueryBuilderInterface
{
    const LEFT_JOIN = 'LEFT JOIN';
    const INNER_JOIN = 'INNER JOIN';
    const RIGHT_JOIN = 'RIGHT JOIN';

    protected array $bindings;
    protected array $blocks = [
        'select' => [],
        'from' => '',
        'join' => [],
        'where' => [],
        'orderBy' => [],
        'limit' => null,
        'offset' => null,
    ];

    private function buildSql(): string
    {
        $sql = '';

        foreach ($this->blocks as $key => $value) {
            if (empty($value) === false) {
                $sql .= $this->blockToSql($key, $value);
                $sql .= ' ';
            }
        }

        return $sql;
    }

    private function blockToSql(string $key, mixed $value): string
    {
        return match ($key) {
            'where' => 'WHERE ' . implode(' AND ', $value),
            'join' => implode(' ', $value),
            'orderBy' => 'ORDER BY ' . implode(', ', $value),
            'from', 'limit', 'offset' => strtoupper($key) . " $value",
            default => strtoupper($key) . ' ' . implode(', ', $value),
        };
    }

    public function select(array|string ...$fields): static
    {
        foreach ($fields as $field) {
            [$alias, $column] = $this->getNameWithAlias($field);

            if ($alias !== '') {
                $this->blocks['select'][] = "$column as $alias";
                continue;
            }

            $this->blocks['select'][] = $column;
        }

        return $this;
    }

    public function from(array|string $resource): static
    {
        [$alias, $resource] = $this->getNameWithAlias($resource);

        $this->blocks['from'] = "$resource $alias";

        return $this;
    }

    public function where(array $condition): static
    {
        foreach ($condition as $key => $value) {
            if (is_array($value) === true) {
                $this->whereIn($key, $value);
                continue;
            }

            $this->bindParam($key, $value);

            $this->blocks['where'][] = "$key = :$key";
        }

        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        $list = [];

        foreach ($values as $key => $value) {
            $this->bindParam("in$column$key", $value);
            $list[] = ":in$column$key";
        }

        $list = implode(', ', $list);

        $this->blocks['where'][] = "$column IN($list)";

        return $this;
    }

    private function bindParam(string $bind, mixed $value): void
    {
        if (is_string($value) === true) {
            $value = "'$value'";
        }

        $this->bindings[":$bind"] = $value;
    }

    public function join(string $type, string|array $resource, string $on): static
    {
        [$alias, $resource] = $this->getNameWithAlias($resource);

        $this->blocks['join'][] = "$type $resource $alias ON $on";

        return $this;
    }

    public function orderBy(array $columns): static
    {
        $this->blocks['orderBy'] = $columns;

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->blocks['limit'] = $limit;

        return $this;
    }

    public function offset(int $offset): static
    {
        $this->blocks['offset'] = $offset;

        return $this;
    }

    public function getStatement(): StatementParameters
    {
        return new StatementParameters($this->buildSql(), $this->bindings);
    }

    private function getNameWithAlias(string|array $name): array
    {
        $alias = '';

        if (is_array($name) === true) {
            $alias = key($name);
            $name = current($name);
        }

        return [$alias, $name];
    }
}
