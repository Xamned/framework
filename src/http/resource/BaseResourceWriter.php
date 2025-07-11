<?php

namespace xamned\framework\http\resource;

use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\contracts\http\resource\ResourceWriterInterface;

abstract class BaseResourceWriter implements ResourceWriterInterface
{
    private string $resourceName;

    private readonly DataBaseConnectionInterface $dbConnection;

    protected function setDbConnection(DataBaseConnectionInterface $dbConnection): void
    {
        $this->dbConnection = $dbConnection;
    }

    public function setResourceName(string $name): static
    {
        $this->resourceName = $name;

        return $this;
    }

    public function create(array $values): void
    {
        $this->dbConnection->insert($this->resourceName, $values);
    }

    public function update(array $condition, array $values): void
    {
        $this->dbConnection->update($this->resourceName, $values, $condition);
    }

    public function patch(array $condition, array $values): void
    {
        $this->dbConnection->update($this->resourceName, $values, $condition);
    }

    public function delete(array $condition): void
    {
        $this->dbConnection->delete($this->resourceName, $condition);
    }
}