<?php

namespace xamned\framework\db\mysql;

use InvalidArgumentException;
use xamned\framework\contracts\db\MysqlQueryBuilderInterface;
use xamned\framework\db\mysql\enums\ComparisonOperator;
use xamned\framework\db\mysql\enums\LogicOperator;

class QueryBuilder implements MysqlQueryBuilderInterface
{
    protected array $bindings = [];
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

    public function select(array|string $fields): static
    {
        if (is_string($fields) === true) {
            $fields = explode(',', $fields);
        }

        foreach ($fields as $alias => $field) {
            if (is_string($alias) === true) {
                $this->blocks['select'][] = "$field as $alias";
                continue;
            }

            $this->blocks['select'][] = $field;
        }

        return $this;
    }

    public function from(array|string $resource): static
    {
        [$alias, $resource] = $this->getNameWithAlias($resource);

        if ($alias !== null) {
            $this->blocks['from'] = "$resource $alias";
            return $this;
        }

        $this->blocks['from'] = $resource;

        return $this;
    }

    public function where(array $condition): static
    {
        foreach ($condition as $key => $value) {
            if (is_int($key) === true) {
                $this->whereOperator(...$value);
                continue;
            }

            if (is_array($value) === true) {
                $this->whereIn($key, $value);
                continue;
            }

            $this->whereOperator('=', $key, $value);
        }

        return $this;
    }

    protected function whereOperator(string $operator, string $column, mixed $value): static
    {
        $operator = strtolower($operator);

        if ($operator === 'in') {
            return $this->whereIn($column, $value);
        }

        if ($operator === 'like') {
            return $this->whereLike($column, $value);
        }

        if (ComparisonOperator::tryFrom($operator) !== null) {
            return $this->whereComparison($operator, $column, $value);
        }

        throw new InvalidArgumentException("Оператор \"$operator\" не поддерживается");
    }

    protected function whereComparison(string $operator, string $column, mixed $value): static
    {
        if ($value === null) {
            return $this->whereNull($operator, $column);
        }

        $bind = $this->bindParam($column, $value);

        $this->blocks['where'][] = "$column $operator :$bind";

        return $this;
    }

    protected function whereNull(string $operator, string $column): static
    {
        $operator = match ($operator) {
            '=' => 'IS NULL',
            '!=' => 'IS NOT NULL',
            default => throw new InvalidArgumentException("Оператор \"$operator\" не поддерживается c NULL")
        };

        $this->blocks['where'][] = "$column $operator";

        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        $list = [];

        foreach ($values as $key => $value) {
            $bind = $this->bindParam("in$column$key", $value);
            $list[] = ":$bind";
        }

        $list = implode(', ', $list);

        $this->blocks['where'][] = "$column IN($list)";

        return $this;
    }

    public function whereLike(string $column, mixed $value): static
    {
        $this->blocks['where'][] = "$column LIKE '%$value%'";

        return $this;
    }

    private function bindParam(string $bind, mixed $value): string
    {
        $bind = str_replace('.', '__', $bind);

        if (isset($this->bindings[":$bind"]) === true) {
            $bind = $this->resolveBindName($bind);
        }

        $this->bindings[":$bind"] = $value;

        return $bind;
    }

    private function resolveBindName(string $bind): string
    {
        $suffix = count($this->bindings);

        while (isset($this->bindings[":$bind$suffix"]) === true) {
            $suffix++;
        }

        return "$bind$suffix";
    }

    public function join(string $type, string|array $resource, string $on): static
    {
        [$alias, $resource] = $this->getNameWithAlias($resource);

        if ($alias !== null) {
            $this->blocks['join'][] = "$type JOIN $resource $alias ON $on";
            return $this;
        }

        $this->blocks['join'][] = "$type JOIN $resource ON $on";

        return $this;
    }

    public function leftJoin(string|array $resource, string $on): static
    {
        return $this->join('LEFT JOIN', $resource, $on);
    }

    public function rightJoin(string|array $resource, string $on): static
    {
        return $this->join('RIGHT JOIN', $resource, $on);
    }

    public function innerJoin(string|array $resource, string $on): static
    {
        return $this->join('INNER JOIN', $resource, $on);
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
        $alias = null;

        if (is_array($name) === true) {
            $alias = key($name);
            $name = current($name);
        }

        return [$alias, $name];
    }
}
