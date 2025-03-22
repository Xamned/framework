<?php

namespace xamned\framework\db\file;

use InvalidArgumentException;
use RuntimeException;
use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\contracts\db\FileQueryBuilderInterface;
use xamned\framework\contracts\db\QueryBuilderInterface;

class Connection implements DataBaseConnectionInterface
{
    protected array $folders;
    protected array $resourceColumns;
    protected int $lastInsertId;

    public function __construct(array $config) 
    {
        $this->folders = $config['folders'];
    }

    /** @param FileQueryBuilderInterface $query */
    private function prepare(QueryBuilderInterface $query)
    {
        $params = $query->getStatement();

        $dataManager = $this->createDataManager($params->resource);

        if (empty($params->whereClause) === false) {
            $dataManager->filter($params->whereClause);
        }

        if (empty($params->orderByClause) === false) {
            $dataManager->orderBy($params->orderByClause);
        }

        if (empty($params->limit) === false) {
            $dataManager->limit($params->limit);
        }

        if (empty($params->offset) === false) {
            $dataManager->offset($params->offset);
        }

        if (empty($params->selectFields) === false) {
            return $dataManager->select($params->selectFields);
        }
        
        throw new InvalidArgumentException("Запрос не может быть реализован без блока SELECT.");
    }

    private function getFileContent(string $resource): FileContent
    {
        foreach ($this->folders as $folder) {
            $file = "$folder/{$resource}.json";

            if (file_exists($file) === true) {
                return new FileContent($file, $this->resourceColumns[$resource] ?? []);
            }
        }

        throw new InvalidArgumentException("Файла с именем {$resource}.json не существует");
    }

    private function createDataManager(string $resource): TableDataManager
    {
        return new TableDataManager($this->getFileContent($resource)->getTableData());
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
        $fileContent = $this->getFileContent($resource);
        $table = $fileContent->getTableData();
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

        file_put_contents($fileContent->file, json_encode(['data' => $table], JSON_UNESCAPED_UNICODE)) 
            ?: throw new RuntimeException('Не удалось обновить файл.');

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

            if (is_array($value) === true && in_array($row[$column], $value) === false) {
                return false;
            }

            if ($row[$column] !== $value) {
                return false;
            }
        }

        return true;
    }

    public function insert(string $resource, array $data): int
    {
        $fileContent = $this->getFileContent($resource);
        $table = $fileContent->getTableData();

        foreach (array_keys($data) as $column) {
            if (isset(current($table)[$column]) === false) {
                throw new InvalidArgumentException("В таблице не существует колонки $column");
            }
        }

        $table[] = $data;

        file_put_contents($fileContent->file, json_encode(['data' => $table], JSON_UNESCAPED_UNICODE)) 
            ?: throw new RuntimeException('Не удалось обновить файл.');

        $this->lastInsertId = array_key_last($table);

        return 1;
    }

    public function delete(string $resource, array $condition): int
    {
        $fileContent = $this->getFileContent($resource);
        $table = $fileContent->getTableData();

        $new = array_filter($table, function ($row) use ($condition) {
            return $this->checkCondition($row, $condition) === false;
        });

        file_put_contents($fileContent->file, json_encode(['data' => $new], JSON_UNESCAPED_UNICODE)) 
            ?: throw new RuntimeException('Не удалось обновить файл.');

        return count($table) - count($new);
    }

    public function getLastInsertId(): string
    {
        return $this->lastInsertId;
    }
}
