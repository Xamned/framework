<?php

namespace xamned\framework\http\resource;

use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\contracts\db\QueryBuilderInterface;
use xamned\framework\contracts\http\resource\ResourceDataFilterInterface;
use xamned\framework\http\exceptions\HttpBadRequestException;

abstract class BaseResourceDataFilter implements ResourceDataFilterInterface
{
    private string $resourceName;
    private readonly DataBaseConnectionInterface $dbConnection;
    private readonly QueryBuilderInterface $queryBuilder;
    private array $accessibleFields = [];
    private array $accessibleFilters = [];
    private array $expands = [];

    protected function setDbConnection(DataBaseConnectionInterface $dbConnection): void
    {
        $this->dbConnection = $dbConnection;
    }

    protected function setQueryBuilder(QueryBuilderInterface $queryBuilder): void
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function setResourceName(string $name): static
    {
        $this->resourceName = $name;
        return $this;
    }

    public function setAccessibleFields(array $fieldNames): static
    {
        $this->accessibleFields = $fieldNames;
        return $this;
    }

    public function setAccessibleFilters(array $filterNames): static
    {
        $this->accessibleFilters = $filterNames;
        return $this;
    }

    public function setExpands(array $expands): static
    {
        $this->expands = $expands;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function filterAll(array $condition): array
    {
        $this->buildQuery($condition);

        $data = $this->dbConnection->select($this->queryBuilder);

        return array_map(fn(array $item): array => $this->hydrate($item, $condition), $data);
    }

    /**
     * @inheritDoc
     */
    public function filterOne(array $condition): ?array
    {
        $this->buildQuery($condition);

        $data = $this->dbConnection->selectOne($this->queryBuilder);

        if ($data === null) {
            return null;
        }

        return $this->hydrate($data, $condition);
    }

    protected function hydrate(array $data, array $condition): array
    {
        $expand = [];

        if (isset($condition['expand']) === true) {
            $expand = explode(',', $condition['expand']);
        }
        
        $item = [];

        foreach ($this->accessibleFields as $field => $value) {
            if (is_array($value) === false) {
                $item[$field] = $data[$field];
                continue;
            }

            $resource = $field;

            if (in_array($resource, $expand) === false) {
                continue;
            }

            $item['relationships'][$resource] = $this->hydrateExpand($resource, $data);
        }

        return $item;
    }

    private function hydrateExpand(string $resource, array $data): array 
    {
        $item = [];

        foreach ($this->accessibleFields[$resource] as $field => $dbField) {
            $alias = $this->getResourceFieldAlias($resource, $field);

            if (array_key_exists($alias, $data) === false) {
                continue;
            }

            $item[$field] = $data[$alias];
        }

        return $item;
    }

    protected function buildQuery(array $condition): void
    {
        $expand = [];

        if (isset($condition['expand']) === true) {
            $expand = explode(',', $condition['expand']);
        }

        $fields = isset($condition['fields']) === true
            ? $this->parseFields($condition['fields'])
            : $this->getDefaultFields($expand);

        $this->queryBuilder
            ->select($fields)
            ->from($this->resourceName);

        foreach ($condition['filter'] ?? [] as $field => $fieldCondition) {
            foreach ($this->mapCondition($field, $fieldCondition) as $condition) {
                $this->queryBuilder->where($condition);
            }
        }

        foreach ($expand as $resource) {
            $this->queryBuilder->join('LEFT', $resource, $this->expands[$resource] 
                ?? throw new HttpBadRequestException("Расширение $resource недоступно"));
        }
    }

    protected function mapCondition(string $field, mixed $condition): array
    {
        $field = $this->accessibleFilters[$field] 
            ?? throw new HttpBadRequestException("Нельзя отфильтровать ресурс по полю $field");

        if (str_contains($field, '.') === false) {
            $field = "{$this->resourceName}.{$field}";
        }

        if (is_array($condition) === false) {
            return [[$field => $condition]];
        }

        $conditions = [];

        foreach ($condition as $operator => $value) {
            $conditions[] = match ($operator) {
                '$eq', '$in' => [$field => $value],
                '$gt' => ['>', $field, $value],
                '$lt' => ['<', $field, $value],
                '$ge' => ['>=', $field, $value],
                '$le' => ['<=', $field, $value],
            };
        }

        return $conditions;
    }

    protected function parseFields(string $value): array
    {
        $fields = [];

        foreach (explode(',', $value) as $field) {
            if ($field === '') {
                continue;
            }

            if (str_contains($field, '.') === true) {
                [$resource, $resourceField] = explode('.', $field, 2);

                $actualField = $this->accessibleFields[$resource][$resourceField]
                    ?? throw new HttpBadRequestException("Поле $field недоступно к получению");

                $fields[$this->getResourceFieldAlias($resource, $field)] = "{$resource}.{$actualField}";
                continue;
            }

            $actualField = $this->accessibleFields[$field] 
                ?? throw new HttpBadRequestException("Поле $field недоступно к получению");

            $fields[$field] = "{$this->resourceName}.{$actualField}";
        }

        return $fields;
    }

    protected function getDefaultFields(array $expand): array
    {
        $fields = [];

        foreach ($this->accessibleFields as $field => $value) {
            if (is_array($value) === false) {
                $fields[$field] = "{$this->resourceName}.{$value}";
                continue;
            }

            $resource = $field;

            if (in_array($resource, $expand) === false) {
                continue;
            }

            foreach ($value as $field => $dbField) {
                $fields[$this->getResourceFieldAlias($resource, $field)] = "{$resource}.{$dbField}";
            }
        }

        return $fields;
    }

    protected function getResourceFieldAlias(string $resource, string $field): string
    {
        return $resource . ucfirst($field);
    }
}
