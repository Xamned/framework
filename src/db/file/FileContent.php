<?php

namespace xamned\framework\db\file;

class FileContent
{
    protected array $data = [];
    protected array $row = [];

    public function __construct(
        public readonly string $file, 
        array $columns = []
    ) {
        $this->format(
            json_decode(file_get_contents($this->file), true, flags:JSON_THROW_ON_ERROR),
            $columns
        );
    }

    public function getTableData(): array
    {
        return $this->data;
    }

    private function format(array $data, array $columns = []): void
    {
        $count = count(array_keys($data));

        match (true) {
            $columns !== [] => $this->treesFormat($data, $columns),
            isset($data['data']) === true && $count === 1 => $this->simpleFormat($data),
            isset($data['data'], $data['columns']) === true && $count === 2 => $this->tableFormat($data),
            true => throw new \InvalidArgumentException('Невозможно определить способ форматирования данных.')
        };
    }

    /**
     * Форматирование таблицы, где (data = строки) и (ключи строк = колонки)
     * 
     * Пример:
     * {
     *      "data": [
     *	        {"a": 2, "b": 3, "c": 4, "d": 1},
     *	        ...
     *      ]
     * }
     * @param array $data
     * @return void
     */
    private function simpleFormat(array $data): void
    {
        $this->data = $data['data'];
    }

    /**
     * Форматирование таблицы, где (columns = имена колонок) и (data = строки)
     * 
     * Пример:
     * {
     *      "columns": ["a", "b", "c", "d"],
     *      "data": [
     *	         [2, 3, 4, 1],
     *	        ...
     *      ]
     * }
     * @param array $data
     * @return void
     */
    private function tableFormat(array $data): void
    {
        foreach ($data['data'] as $row) {
            $this->data[] = array_combine($data['columns'], $row);
        }
    }

    /**
     * Форматирование таблицы, где (уровни глубины = колонки)
     * 
     * Пример:
     * {
     *      "type1": {
     *          "month1": {
     *              "ton1": 50,
     *              "ton2": 20,
     *              "ton3": 30,
     *          },
     *	        ...
     *      },
     *      ...
     * }
     * @param array $data
     * @return void
     */
    private function treesFormat(array $data, array $columns, int $depth = 0): void
    {
        $column = $columns[$depth];
        $values = array_keys($data);

        foreach ($values as $value) {
            $this->row[$column] = $value;

            if ($depth < count($columns) - 2) {
                $this->treesFormat($data[$value], $columns, $depth + 1);
                continue;
            }

            if ($depth === count($columns) - 2) {
                $this->row[$columns[$depth + 1]] = $data[$value];
    
                $this->data[] = $this->row;
            }

            array_pop($this->row);
        }
    }
}
