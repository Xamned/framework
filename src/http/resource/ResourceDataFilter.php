<?php

namespace xamned\framework\http\resource;

use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\contracts\db\QueryBuilderInterface;
use xamned\framework\contracts\http\resource\ResourceDataFilterInterface;

class ResourceDataFilter implements ResourceDataFilterInterface
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
        // TODO: Implement filterAll() method.
    }

    /**
     * @inheritDoc
     */
    function filterOne(array $condition): array
    {
        // TODO: Implement filterOne() method.
    }
}