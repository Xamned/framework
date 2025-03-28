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

    public function update(int|string $id, array $values): void
    {
        $this->dbConnection->update($this->resourceName, $values, ['id' => $id]);
    }

    public function patch(int|string $id, array $values): void
    {
        $this->dbConnection->update($this->resourceName, $values, ['id' => $id]);
    }

    public function delete(int|string $id): void
    {
        $this->dbConnection->delete($this->resourceName, ['id' => $id]);
    }
}