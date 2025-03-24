<?php

namespace xamned\framework\http\resource;

use RuntimeException;
use xamned\framework\contracts\http\resource\ResourceDataFilterInterface;

class JsonResourceDataFilter implements ResourceDataFilterInterface
{
    private string $resourceName;
    private array $accessibleFields = [];
    private array $accessibleFilters = [];

    function setResourceName(string $name): static
    {
        $this->resourceName = $name;
        return $this;
    }

    function setAccessibleFields(array $fieldNames): static
    {
        $this->accessibleFields = $fieldNames;
        return $this;
    }

    function setAccessibleFilters(array $filterNames): static
    {
        $this->accessibleFilters = $filterNames;
        return $this;
    }

    /**
     * @inheritDoc
     */
    function filterAll(array $condition): array
    {
        $data = $this->loadData();
        $filteredData = [];

        foreach ($data as $item) {
            if ($this->matchesCondition($item, $condition)) {
                $filteredData[] = $this->filterFields($item, $condition['fields'] ?? []);
            }
        }

        return $filteredData;
    }

    /**
     * @inheritDoc
     */
    function filterOne(array $condition): array
    {
        $data = $this->loadData();

        foreach ($data as $item) {
            if ($this->matchesCondition($item, $condition)) {
                return [$this->filterFields($item, $condition['fields'] ?? [])];
            }
        }

        return [];
    }

    private function loadData(): array
    {
        $filename = $this->resourceName . '.json';
        if (file_exists($filename) === false) {
            throw new RuntimeException("Файл $filename не найден.");
        }

        $json = file_get_contents($filename);
        return json_decode($json, true);
    }

    private function matchesCondition(array $item, array $condition): bool
    {
        foreach ($condition['filter'] as $field => $filters) {
            if (in_array($field, $this->accessibleFilters) === false) {
                continue;
            }

            foreach ($filters as $operator => $value) {
                if ($this->compare($item[$field] ?? null, $operator, $value) === false) {
                    return false;
                }
            }
        }

        return true;
    }

    private function compare($itemValue, string $operator, $conditionValue): bool
    {
        return match ($operator) {
            '$eq' => $itemValue === $conditionValue,
            default => throw new RuntimeException("Неизвестный оператор: $operator"),
        };
    }

    private function filterFields(array $item, array $fields): array
    {
        if (empty($fields) === true) {
            return $item;
        }

        $filteredItem = [];
        foreach ($fields as $field) {
            if (in_array($field, $this->accessibleFields) === true) {
                $filteredItem[$field] = $item[$field] ?? null;
            }
        }

        return $filteredItem;
    }
}