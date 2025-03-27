<?php

namespace xamned\framework\http\resource;

use InvalidArgumentException;
use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\contracts\db\QueryBuilderInterface;
use xamned\framework\contracts\http\resource\ResourceDataFilterInterface;

class ResourceDataFilter implements ResourceDataFilterInterface
{
    private string $resourceName;
    private array $accessibleFields = [];
    private array $accessibleFilters = [];

    public function __construct(
        private readonly DataBaseConnectionInterface $dbConnection,
        private readonly QueryBuilderInterface $queryBuilder,
    ) {

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
    public function filterOne(array $condition): array
    {
        $this->checkConditionFilter($condition);

        return $this->dbConnection->selectOne(
            $this->buildQuery($condition)
        );
    }

    private function buildQuery(array $condition): QueryBuilderInterface
    {
        $query = $this->queryBuilder
            ->select([$condition['fields']])
            ->from($this->resourceName);

        foreach ($condition['filter'] as $field => $fieldCondition) {
            $query->where($this->mapCondition($field, $fieldCondition));
        }

        return $query;
    }

    private function checkConditionFilter(array $condition): void
    {
        foreach ($condition['filter'] as $field => $fieldCondition) {
            if (in_array($field, $this->accessibleFields) === false) {
                throw new InvalidArgumentException('Поле ' . $field . ' недоступно');
            }

            if (in_array($field, $this->accessibleFilters) === false) {
                throw new InvalidArgumentException('Нельзя отфильтровать ресурс по полю ' . $field);
            }
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

        return $queryReadableConditions;
    }
}