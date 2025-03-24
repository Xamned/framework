<?php

namespace xamned\framework\http\resource;

use InvalidArgumentException;
use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\contracts\db\QueryBuilderInterface;
use xamned\framework\contracts\http\resource\ResourceDataFilterInterface;

class DatabaseResourceDataFilter implements ResourceDataFilterInterface
{
    private string $resourceName;
    private array $accessibleFields = [];
    private array $accessibleFilters = [];

    public function __construct(
        private DataBaseConnectionInterface $dbConnection,
        private QueryBuilderInterface $queryBuilder,
    ) {

    }

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
        return $this->dbConnection->select(
            $this->buildQuery($condition)
        );
    }

    /**
     * @inheritDoc
     */
    function filterOne(array $condition): array
    {
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
            if (
                in_array($field, $this->accessibleFields) === true
                && in_array('$eq', $this->accessibleFilters) === true
            ) {
                $query->where([$field => $fieldCondition['$eq']]);
            }
        }

        return $query;
    }
}