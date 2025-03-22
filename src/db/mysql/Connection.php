<?php

namespace xamned\framework\db\mysql;

use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\contracts\db\MysqlQueryBuilderInterface;
use xamned\framework\contracts\db\QueryBuilderInterface;

class Connection implements DataBaseConnectionInterface
{
    protected \PDO $pdo;
    protected array $pdoOptions = [
        \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
        \PDO::ATTR_STRINGIFY_FETCHES => false,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];

    public function __construct(array $config) 
    {
        $this->pdo = new \PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbName']}",
            $config['dbUser'],
            $config['dbPassword'],
            array_merge($this->pdoOptions, $config['options'] ?? []),
        );
    }

    private function prepare(MysqlQueryBuilderInterface $query): \PDOStatement
    {
        $params = $query->getStatement();

        $statement = $this->pdo->prepare($params->sql);

        $statement->execute($params->bindings);

        return $statement;
    }

    public function select(QueryBuilderInterface $query): array
    {
        return $this->prepare($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function selectOne(QueryBuilderInterface $query): null|array
    {
        return $this->prepare($query)->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function selectColumn(QueryBuilderInterface $query): array
    {
        return $this->prepare($query)->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function selectScalar(QueryBuilderInterface $query): mixed
    {
        return $this->prepare($query)->fetchColumn();
    }

    public function update(string $resource, array $data, array $condition): int
    {
        $bindings = [];

        $setSql = implode(', ', $this->bindData($data, $bindings));

        $whereSql = implode(' AND ', $this->bindData($condition, $bindings, 'W'));

        $statement = $this->pdo->prepare("UPDATE $resource SET $setSql WHERE $whereSql");

        $statement->execute($bindings);

        return $statement->rowCount();
    }

    private function bindData(array $data, array &$bindings, $bindPrefix = 'S'): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $bindings[":$bindPrefix$key"] = $value;

            $result[] = "$key = :$bindPrefix$key";
        }

        return $result;
    }

    public function insert(string $resource, array $data): int
    {
        $keys = array_keys($data);

        $columns = implode(', ', $keys);

        $values = implode(', ', array_map(fn($value) => ":$value", $keys));

        $statement = $this->pdo->prepare("INSERT INTO $resource ($columns) VALUES ($values)");

        $statement->execute($data);

        return $statement->rowCount();
    }

    public function delete(string $resource, array $condition): int
    {
        $bindings = [];

        $whereSql = implode(' AND ', $this->bindData($condition, $bindings));

        $statement = $this->pdo->prepare("DELETE FROM $resource WHERE $whereSql");

        $statement->execute($bindings);

        return $statement->rowCount();
    }

    public function getLastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}
