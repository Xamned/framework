<?php

namespace xamned\framework\db\file;

use InvalidArgumentException;
use xamned\framework\db\mysql\enums\ComparisonOperator;

class TableDataFilter
{
    public int $limit;
    public int $offset;

    public function __construct(protected array $table) {}

    public function filter(array $where): void
    {
        foreach ($where as $condition) {
            $column = key($condition);
            $value = current($condition);

            $this->issetColumn($column);

            $this->table = array_filter($this->table, function (array $row) use ($column, $value) {
                if (is_int($column) === true) {
                    [$operator, $column, $value] = $value;

                    $comparisonOperator = ComparisonOperator::tryFrom($operator) 
                        ?? throw new InvalidArgumentException("Оператор \"$operator\" не поддерживается");

                    return $comparisonOperator->compare($row[$column], $value);
                }

                if (is_array($value) === true) {
                    return in_array($row[$column], $value) === true;
                }

                return $row[$column] === $value;
            });
        }
    }

    public function orderBy(array $columns): void
    {
        foreach ($columns as $column) {
            $this->issetColumn($column);

            usort($this->table, function($firstRow, $secondRow) use ($column): int {
                if ($firstRow[$column] === $secondRow[$column]) {
                    return 0;
                }

                if (is_string($firstRow[$column]) === true) {
                    return strcmp($firstRow[$column], $secondRow[$column]) < 0 ? -1 : 1;
                }

                if (is_numeric($firstRow[$column]) === true || is_bool($firstRow[$column]) === true) {
                    return $firstRow[$column] < $secondRow[$column] ? -1 : 1;
                }

                return 0;
            });
        }
    }

    public function limit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function offset(int $offset): void
    {
        $this->offset = $offset;
    }

    public function select(array $columns): array
    {
        $result = [];

        $columns = $this->prepareColumns($columns);

        foreach ($this->table as $row) {
            $newRow = [];
            
            foreach ($columns as $alias => $column) {
                $newRow[$alias] = $row[$column];
            }
            
            $result[] = $newRow;
        }

        return array_slice($result, $this->offset ?? 0, $this->limit ?? null);
    }

    private function prepareColumns(array $columns): array
    {
        $aliases = [];

        foreach ($columns as $alias => $column) {
            $this->issetColumn($column);

            if (is_int($alias) === true) {
                $aliases[] = $column;
                continue;
            }

            $aliases[] = $alias;
        }
        
        return array_combine($aliases, $columns);
    }

    private function issetColumn(string $column): void
    {
        if (isset(current($this->table)[$column]) === false) {
            throw new InvalidArgumentException("В таблице не существует колонки $column");
        }
    }
}
