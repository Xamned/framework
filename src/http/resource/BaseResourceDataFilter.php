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

        return $this->dbConnection->select(
            $this->buildQuery($condition)
        );
    }

    /**
     * @inheritDoc
     */
    public function filterOne(array $condition): ?array
    {
        $this->checkConditionFilter($condition);

        return $this->dbConnection->selectOne(
            $this->buildQuery($condition)
        );
    }

    private function buildQuery(array $condition): QueryBuilderInterface
    {
        $query = $this->queryBuilder
            ->select($condition['fields'] ?? $this->accessibleFields)
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

    /**
     * @throws HttpBadRequestException
     */
    private function checkValidExpand(string $expand): void
    {
        if (in_array($expand, $this->expands) === false) {
            throw new HttpBadRequestException('Указанного к расширению ресурса ' . $expand . ' не существует');
        }
    }

    private function mapCondition(string $field, array $fieldCondition): array
    {
        $queryReadableConditions = [];

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