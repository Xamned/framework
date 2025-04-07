<?php

namespace xamned\framework\db\file;

use xamned\framework\contracts\db\FileQueryBuilderInterface;

class QueryBuilder implements FileQueryBuilderInterface
{
    protected array $blocks = [
        'select' => [],
        'from' => '',
        'join' => [],
        'where' => [],
        'orderBy' => [],
        'limit' => null,
        'offset' => null,
    ];

    public function select(array|string $fields): static
    {
        $this->blocks['select'] = $fields;

        return $this;
    }

    public function from(array|string $resource): static
    {
        $this->blocks['from'] = is_string($resource) 
            ? $resource 
            : current($resource);

        return $this;
    }

    public function where(array $condition): static
    {
        foreach ($condition as $column => $value) {
            $this->blocks['where'][] = [$column => $value];
        }

        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        $this->blocks['where'][] = [$column => $values];

        return $this;
    }

    public function join(string $type, string|array $resource, string $on): static
    {
        throw new \LogicException('Невозможно реализовать метод.');
    }

    public function orderBy(array $columns): static
    {
        $this->blocks['orderBy'][] = $columns;

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
        return new StatementParameters(
            $this->blocks['from'],
            $this->blocks['select'],
            $this->blocks['where'],
            $this->blocks['orderBy'],
            $this->blocks['limit'],
            $this->blocks['offset'],
        );
    }
}
