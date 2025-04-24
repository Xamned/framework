<?php

namespace xamned\framework\http\resource;

use InvalidArgumentException;
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
        $this->checkConditionFilter($condition);
        $this->checkConditionFields($condition);

        $results = $this->dbConnection->select(
            $this->buildQuery($condition)
        );

        return $this->transformResults($results, $condition['expand'] ?? null);
    }

    /**
     * @inheritDoc
     */
    public function filterOne(array $condition): ?array
    {
        $this->checkConditionFilter($condition);
        $this->checkConditionFields($condition);

        $result = $this->dbConnection->selectOne(
            $this->buildQuery($condition)
        );

        return $result ? $this->transformResult($result, $condition['expand'] ?? null) : null;
    }

    private function transformResults(array $results, ?string $expand): array
    {
        return array_map(function($item) use ($expand) {
            return $this->transformResult($item, $expand);
        }, $results);
    }

    private function addExpandFields(string $expandingResource, QueryBuilderInterface $query, array $fields): void
    {
        foreach ($fields as $field) {
            list($resource, $resourceField) = explode('.', $field, 2);

            if ($resource === $expandingResource) {
                $alias = $expandingResource . '__' . $resourceField;
                $query->select([$alias => $expandingResource . '.' . $resourceField]);
            }
        }
    }

    private function transformResult(array $item, ?string $expand): array
    {
        $transformed = [];
        $relationships = [];

        if ($expand === null) {
            return $item;
        }

        $expandingResources = explode(',', $expand);

        foreach ($item as $key => $value) {
            if (str_contains($key, '__')) {
                list($resource, $field) = explode('__', $key, 2);

                if (in_array($resource, $expandingResources)) {
                    if (!isset($relationships[$resource])) {
                        $relationships[$resource] = [];
                    }
                    $relationships[$resource][$field] = $value;
                    continue;
                }
            }

            $transformed[$key] = $value;
        }

        $result = $transformed;

        if (!empty($relationships)) {
            $result['relationships'] = $relationships;
        }

        return $result;
    }

    private function buildQuery(array $condition): QueryBuilderInterface
    {
        $requestedFields = [];
        $requestedExpandFields = [];

        if (isset($condition['fields']) === true) {
            $requestedFields = explode(',', $condition['fields']);

            $requestedExpandFields = array_filter($requestedFields, function ($field) {
                return str_contains($field, '.') === true;
            });

            $requestedFields = array_filter($requestedFields, function ($field) {
                return str_contains($field, '.') === false;
            });
        }

        $query = $this->queryBuilder
            ->select( empty($requestedFields) === true
                ? array_filter($this->accessibleFields, function($field) {return is_array($field) === false;})
                : $requestedFields
            )
            ->from($this->resourceName);

        if (isset($condition['filter']) === true) {
            foreach ($condition['filter'] as $field => $fieldCondition) {
                $query->where($this->mapCondition($field, $fieldCondition));
            }
        }

        if (isset($condition['expand']) === true) {
            $expandingResources = explode(',', $condition['expand']);

            foreach ($expandingResources as $expandingResource) {
                $this->checkValidExpand($expandingResource);

                $query->join('LEFT', $expandingResource, $this->expands[$expandingResource]);

                $this->addExpandFields(
                    $expandingResource,
                    $query,
                    $requestedExpandFields
                );
            }
        }

        return $query;
    }

    private function checkConditionFilter(array $condition): void
    {
        if (isset($condition['filter']) === false) {
            return;
        }

        foreach ($condition['filter'] as $field => $fieldCondition) {
            if (in_array($field, $this->accessibleFields) === false) {
                throw new InvalidArgumentException('Поле ' . $field . ' недоступно');
            }

            if (in_array($field, $this->accessibleFilters) === false) {
                throw new InvalidArgumentException('Нельзя отфильтровать ресурс по полю ' . $field);
            }
        }
    }

    private function checkConditionFields(array $condition): void
    {
        if (isset($condition['fields']) === false) {
            return;
        }

        foreach (explode(',', $condition['fields']) as $field) {
            if (str_contains($field, '.') === true) {
                list($resource, $resourceField) = explode('.', $field, 2);

                if (array_key_exists($resource, $this->accessibleFields) === false) {
                    throw new InvalidArgumentException('Поля ресурса ' . $resource . ' недоступны к получению');
                }

                if (in_array($resourceField, $this->accessibleFields[$resource]) === false) {
                    throw new InvalidArgumentException('Поле ' . $resourceField . ' из ' . $resource . ' недоступно к получению');
                }
            } else {
                if (array_key_exists($field, $this->accessibleFields) === false) {
                    throw new InvalidArgumentException('Поле ' . $field . ' недоступно к получению');
                }
            }
        }
    }

    /**
     * @throws HttpBadRequestException
     */
    private function checkValidExpand(string $expand): void
    {
        if (array_key_exists($expand, $this->expands) === false) {
            throw new HttpBadRequestException('Расширение ресурса ' . $expand . ' недоступно');
        }

        if (array_key_exists($expand, $this->accessibleFields) === false) {
            throw new HttpBadRequestException('У ресурса ' . $expand . ' нет полей доступных к расширению');
        }
    }

    private function mapCondition(string $field, mixed $fieldCondition): array
    {
        $queryReadableConditions = [];

        if (is_array($fieldCondition) === false) {
            return [$field => $fieldCondition];
        }

        foreach ($fieldCondition as $operator => $conditionValue) {
            $queryReadableConditions[] = match ($operator) {
                '$eq', '$in' => [$field => $conditionValue],
                '$gt' => ['>', $field, $conditionValue],
                '$lt' => ['<', $field, $conditionValue],
                '$ge' => ['>=', $field, $conditionValue],
                '$le' => ['<=', $field, $conditionValue],
            };
        }

        return reset($queryReadableConditions);
    }
}