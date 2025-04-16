<?php

namespace xamned\framework\db\file;

use InvalidArgumentException;
use RuntimeException;
use xamned\framework\contracts\db\FileConnectionInterface;
use xamned\framework\contracts\db\FileQueryBuilderInterface;
use xamned\framework\contracts\db\ListBuilderFactoryInterface;
use xamned\framework\contracts\db\ListBuilderInterface;
use xamned\framework\contracts\db\QueryBuilderInterface;
use xamned\framework\db\file\enums\FileTypeEnum;
use xamned\framework\db\mysql\enums\ComparisonOperator;

class Connection implements FileConnectionInterface
{
    protected array $folders;
    protected array $resourceColumns;
    protected FileTypeEnum $fileType;
    protected ListBuilderFactoryInterface $listBuilderFactory;

    protected int $lastInsertId;

    public function __construct(array $config) 
    {
        $this->folders = $config['folders'];
        $this->resourceColumns = $config['resourceColumns'];
        $this->fileType = $config['fileType'];
        $this->listBuilderFactory = $config['listBuilderFactory'];
    }

    /** @param FileQueryBuilderInterface $query */
    private function prepare(QueryBuilderInterface $query): array
    {
        $params = $query->getStatement();

        $dataFilter = $this->createDataFilter($params->resource);

        if (empty($params->whereClause) === false) {
            $dataFilter->filter($params->whereClause);
        }

        if (empty($params->orderByClause) === false) {
            $dataFilter->orderBy($params->orderByClause);
        }

        if (empty($params->limit) === false) {
            $dataFilter->limit($params->limit);
        }

        if (empty($params->offset) === false) {
            $dataFilter->offset($params->offset);
        }

        if (empty($params->selectFields) === false) {
            return $dataFilter->select($params->selectFields);
        }
        
        throw new InvalidArgumentException("Запрос не может быть реализован без блока SELECT.");
    }

    private function getListBuilder(string $resource): ListBuilderInterface
    {
        foreach ($this->folders as $folder) {
            $fileName = "$folder/{$resource}.{$this->fileType->value}";

            if (file_exists($fileName) === true) {
                return $this->listBuilderFactory->create(
                    $this->fileType, 
                    $fileName, 
                    $this->resourceColumns[$resource] ?? []
                );
            }
        }

        throw new InvalidArgumentException("Файла с именем {$resource}.{$this->fileType->value} не существует");
    }

    private function createDataFilter(string $resource): TableDataFilter
    {
        return new TableDataFilter($this->getListBuilder($resource)->getData());
    }

    /** @param FileQueryBuilderInterface $query */
    public function select(QueryBuilderInterface $query): array
    {
        return $this->prepare($query);
    }

    /** @param FileQueryBuilderInterface $query */
    public function selectOne(QueryBuilderInterface $query): null|array
    {
        return current($this->prepare($query)) ?: null;
    }

    /** @param FileQueryBuilderInterface $query */
    public function selectColumn(QueryBuilderInterface $query): array
    {
        $data = $this->prepare($query);

        return array_column($data, key(current($data)));
    }

    /** @param FileQueryBuilderInterface $query */
    public function selectScalar(QueryBuilderInterface $query): mixed
    {
        return current(current($this->prepare($query)));
    }

    public function update(string $resource, array $data, array $condition): int
    {
        $listBuilder = $this->getListBuilder($resource);
        $table = $listBuilder->getData();
        $count = 0;

        foreach ($table as &$row) {
            if ($this->checkCondition($row, $condition) === false) {
                continue;
            }

            foreach ($data as $column => $value) {
                $row[$column] = $value;
            }

            $count++;
        }
        unset($row);

        $this->updateFile($listBuilder, $table);

        return $count;
    }

    private function checkCondition(array $row, array $where)
    {
        foreach ($where as $condition) {
            $column = key($condition);
            $value = current($condition);

            if (isset($row[$column]) === false) {
                throw new InvalidArgumentException("В таблице не существует колонки $column");
            }

            if (is_int($column) === true && $this->compareByOperator($value, $row) === false) {
                return false;
            }

            if (is_array($value) === true && in_array($row[$column], $value) === false) {
                return false;
            }

            if ($row[$column] !== $value) {
                return false;
            }
        }

        return true;
    }

    private function compareByOperator(array $condition, array $row): bool
    {
        [$operator, $column, $value] = $condition;

        $comparisonOperator = ComparisonOperator::tryFrom($operator) 
            ?? throw new InvalidArgumentException("Оператор \"$operator\" не поддерживается");

        return $comparisonOperator->compare($row[$column], $value);
    }

    public function insert(string $resource, array $data): int
    {
        $listBuilder = $this->getListBuilder($resource);
        $table = $listBuilder->getData();

        foreach (array_keys($data) as $column) {
            if (isset(current($table)[$column]) === false) {
                throw new InvalidArgumentException("В таблице не существует колонки $column");
            }
        }

        $table[] = $data;

        $this->updateFile($listBuilder, $table);

        $this->lastInsertId = array_key_last($table);

        return 1;
    }

    public function delete(string $resource, array $condition): int
    {
        $listBuilder = $this->getListBuilder($resource);
        $table = $listBuilder->getData();

        $updatedTable = array_filter($table, function ($row) use ($condition) {
            return $this->checkCondition($row, $condition) === false;
        });

        $this->updateFile($listBuilder, $updatedTable);

        return count($table) - count($updatedTable);
    }

    public function getLastInsertId(): string
    {
        return $this->lastInsertId;
    }

    private function updateFile(ListBuilderInterface $listBuilder, array $data): void
    {
        file_put_contents($listBuilder->getFileName(), $listBuilder->encode($data)) 
            ?: throw new RuntimeException('Не удалось обновить файл.');
    }
}
