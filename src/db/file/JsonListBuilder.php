<?php

namespace xamned\framework\db\file;

class JsonListBuilder extends AbstractListBuilder
{
    protected array $row = [];

    public function encode(array $data): mixed
    {
        return json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
    }

    protected function decode(): array
    {
        return json_decode(file_get_contents($this->file), true, flags:JSON_THROW_ON_ERROR);
    }

    protected function build(array $data, array $columns = []): void
    {
        $count = count(array_keys($data));

        if ($columns !== []) {
            $this->fromNestedObject($data, $columns);
            return;
        }

        if (isset($data['data']) === true && $count === 1) {
            $this->fromRows($data);
            return;
        }

        if (isset($data['data'], $data['columns']) === true && $count === 2) {
            $this->fromColumnsAndRows($data);
            return;
        }

        throw new \InvalidArgumentException('Невозможно определить способ форматирования данных.');
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
    private function fromRows(array $data): void
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
    private function fromColumnsAndRows(array $data): void
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
    private function fromNestedObject(array $data, array $columns, int $depth = 0): void
    {
        if (count($columns) === 1) {
            $this->data = array_map(
                function ($item) use ($columns): array {
                    return [$columns[0] => $item];
                },
                $data
            );
            return;
        }

        $column = $columns[$depth];
        $values = array_keys($data);

        foreach ($values as $value) {
            $this->row[$column] = $value;

            if ($depth < count($columns) - 2) {
                $this->fromNestedObject($data[$value], $columns, $depth + 1);
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
